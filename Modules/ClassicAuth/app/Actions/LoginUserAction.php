<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\ClassicAuth\DataTransferObjects\LoginCredentials;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle user login authentication action.
 *
 * This action encapsulates:
 * - Authentication logic
 * - Rate limiting (IP and email-based)
 * - Session management
 * - Event dispatching
 */
final class LoginUserAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    private const MAX_EMAIL_ATTEMPTS = 15;

    private const EMAIL_DECAY_SECONDS = 3600;

    /**
     * Execute the login action.
     *
     *
     * @throws ValidationException
     * @throws TooManyRequestsException
     */
    public function execute(LoginCredentials $credentials): \Illuminate\Contracts\Auth\Authenticatable
    {
        // Check IP-based rate limiting
        try {
            $this->rateLimit(self::MAX_ATTEMPTS, self::DECAY_SECONDS);
        } catch (TooManyRequestsException $e) {
            throw $e;
        }

        // Attempt authentication
        if (! Auth::attempt($credentials->toAuthArray(), $credentials->remember)) {
            // On failure, apply email-based rate limiting
            try {
                $this->rateLimitByEmail(
                    self::MAX_EMAIL_ATTEMPTS,
                    $this->longDuration(90, self::EMAIL_DECAY_SECONDS),
                    $credentials->email,
                    'login'
                );
            } catch (TooManyRequestsException $e) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Handle successful login
        return $this->handleSuccessfulLogin($credentials->remember);
    }

    /**
     * Handle successful login.
     */
    private function handleSuccessfulLogin(bool $remember): \Illuminate\Contracts\Auth\Authenticatable
    {
        $user = Auth::user();

        DB::transaction(function () use ($user) {
            // Clear rate limiters
            $this->clearRateLimiter();
            $this->clearRateLimiter('attemptLogin');

            // Clear email-based rate limiter
            $emailKey = 'login:email:'.mb_strtolower($user->email);
            $this->clearRateLimiterByKey($emailKey);

            // Regenerate session for security
            request()->session()->regenerate();

            // Update last login timestamp
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Clear any lingering authentication data
            session()->forget(['login.email', 'login.attempts']);
        });

        // Dispatch login event
        event(new Login(Auth::guard(), $user, $remember));
        event(new Authenticated(Auth::guard(), $user));

        return $user;
    }
}
