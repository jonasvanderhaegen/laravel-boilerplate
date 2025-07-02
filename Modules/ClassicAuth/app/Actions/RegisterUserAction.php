<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Modules\ClassicAuth\DataTransferObjects\RegisterCredentials;
use Modules\ClassicAuth\DataTransferObjects\RegisterResult;
use Modules\ClassicAuth\Events\Registration\RegistrationFailed;
use Modules\ClassicAuth\Events\Registration\UserRegistered;
use Modules\ClassicAuth\Events\Registration\UserRegistering;
use Modules\ClassicAuth\Events\Security\TooManyFailedAttempts;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle user registration action.
 *
 * This action encapsulates:
 * - User creation logic
 * - Rate limiting (IP-based)
 * - Password hashing
 * - Event dispatching
 * - Timing attack prevention
 */
final class RegisterUserAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 3600;

    public function __construct(
        private readonly Timebox $timebox
    ) {}

    /**
     * Execute the registration action.
     *
     * @throws ValidationException
     * @throws TooManyRequestsException
     */
    public function execute(RegisterCredentials $credentials): RegisterResult
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        // Dispatch registering event
        event(new UserRegistering($credentials, $ipAddress, $userAgent));

        // Check rate limiting
        $this->checkRateLimit();

        // Use timebox to prevent timing attacks
        $minimumMicroseconds = config('classicauth.security.auth_min_time_ms', 300) * 1000;

        return $this->timebox->call(function (Timebox $timebox) use ($credentials, $ipAddress, $userAgent) {
            // Check if email already exists
            if (User::where('email', $credentials->email)->exists()) {
                // Dispatch failed event
                event(new RegistrationFailed(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    'email_taken'
                ));

                throw ValidationException::withMessages([
                    'email' => __('auth.email_taken'),
                ]);
            }

            // Create the user
            $user = DB::transaction(function () use ($credentials) {
                $user = User::create([
                    'name' => $credentials->name,
                    'email' => $credentials->email,
                    'password' => Hash::make($credentials->password),
                ]);

                // Dispatch registered event
                event(new Registered($user));

                return $user;
            });

            // Allow early return for better UX
            $timebox->returnEarly();

            // Log the user in if auto-login is enabled
            $autoLoggedIn = false;
            if (config('classicauth.defaults.auto_login_after_register', true)) {
                Auth::login($user, $credentials->remember);
                $autoLoggedIn = true;
            }

            // Dispatch registered event
            event(new UserRegistered($user, $ipAddress, $userAgent, $autoLoggedIn));

            // Clear rate limiter
            $this->clearRateLimiter();

            return RegisterResult::success($user);
        }, $minimumMicroseconds);
    }

    /**
     * Check IP-based rate limiting.
     *
     * @throws TooManyRequestsException
     */
    private function checkRateLimit(): void
    {
        $maxAttempts = config('classicauth.rate_limiting.register.max_attempts', self::MAX_ATTEMPTS);
        $decaySeconds = config('classicauth.rate_limiting.register.decay_seconds', self::DECAY_SECONDS);

        try {
            $this->rateLimit($maxAttempts, $decaySeconds);
        } catch (TooManyRequestsException $e) {
            // Dispatch security event
            event(new TooManyFailedAttempts(
                'registration',
                request()->ip() ?? 'unknown',
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));

            // Dispatch failed event
            event(new RegistrationFailed(
                '',
                request()->ip() ?? 'unknown',
                request()->userAgent() ?? 'unknown',
                'rate_limited'
            ));

            throw $e;
        }
    }
}
