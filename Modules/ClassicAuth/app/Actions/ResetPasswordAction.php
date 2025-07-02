<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetCredentials;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetResult;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetCompleted;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetFailed;
use Modules\ClassicAuth\Events\Security\TooManyFailedAttempts;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle password reset action.
 *
 * This action encapsulates:
 * - Password reset logic
 * - Token validation
 * - Rate limiting
 * - Event dispatching
 * - Timing attack prevention
 */
final class ResetPasswordAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 900; // 15 minutes

    public function __construct(
        private readonly Timebox $timebox
    ) {}

    /**
     * Execute the password reset action.
     *
     * @throws ValidationException
     * @throws TooManyRequestsException
     */
    public function execute(PasswordResetCredentials $credentials): PasswordResetResult
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';
        
        // Check rate limiting
        $this->checkRateLimit($credentials);

        // Use timebox to prevent timing attacks
        $minimumMicroseconds = config('classicauth.security.auth_min_time_ms', 300) * 1000;

        return $this->timebox->call(function (Timebox $timebox) use ($credentials, $ipAddress, $userAgent) {
            // Reset the password
            $status = Password::reset(
                $credentials->toArray(),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));

                    // Log the user in after reset if enabled
                    if (config('classicauth.defaults.auto_login_after_reset', true)) {
                        Auth::login($user);
                    }
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                $message = match ($status) {
                    Password::INVALID_TOKEN => __('passwords.token'),
                    Password::INVALID_USER => __('passwords.user'),
                    default => __('passwords.reset_failed'),
                };
                
                // Dispatch failed event
                event(new PasswordResetFailed(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    $status === Password::INVALID_TOKEN ? 'invalid_token' : 'invalid_user'
                ));

                throw ValidationException::withMessages([
                    'email' => $message,
                ]);
            }

            // Allow early return for better UX
            $timebox->returnEarly();

            // Clear rate limiters
            $this->clearRateLimiter();
            $this->clearEmailRateLimiter($credentials->email, 'password-reset');
            
            // Dispatch success event
            $user = Auth::user();
            event(new PasswordResetCompleted(
                $user ?? \App\Models\User::where('email', $credentials->email)->first(),
                $ipAddress,
                $userAgent,
                config('classicauth.defaults.auto_login_after_reset', true) && Auth::check()
            ));

            return PasswordResetResult::success($credentials->email);
        }, $minimumMicroseconds);
    }

    /**
     * Check rate limiting.
     *
     * @throws TooManyRequestsException
     */
    private function checkRateLimit(PasswordResetCredentials $credentials): void
    {
        // IP-based rate limiting
        $maxAttempts = config('classicauth.rate_limiting.password_reset.max_attempts', self::MAX_ATTEMPTS);
        $decaySeconds = config('classicauth.rate_limiting.password_reset.decay_seconds', self::DECAY_SECONDS);

        try {
            $this->rateLimit($maxAttempts, $decaySeconds);
        } catch (TooManyRequestsException $e) {
            logger()->warning('Password reset rate limited by IP', [
                'email' => $credentials->email,
                'ip' => request()->ip()
            ]);
            
            // Dispatch security event
            event(new TooManyFailedAttempts(
                'password_reset_complete',
                request()->ip() ?? 'unknown',
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));
            
            // Dispatch failed event
            event(new PasswordResetFailed(
                $credentials->email,
                request()->ip() ?? 'unknown',
                request()->userAgent() ?? 'unknown',
                'rate_limited'
            ));
            
            throw $e;
        }

        // Email-based rate limiting
        try {
            $this->rateLimitByEmail(
                $maxAttempts,
                $this->longDuration(15, $decaySeconds),
                $credentials->email,
                'password-reset-complete'
            );
        } catch (TooManyRequestsException $e) {
            logger()->warning('Password reset rate limited by email', [
                'email' => $credentials->email
            ]);
            
            // Dispatch security event
            event(new TooManyFailedAttempts(
                'password_reset_complete',
                $credentials->email,
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));
            
            throw $e;
        }
    }
}
