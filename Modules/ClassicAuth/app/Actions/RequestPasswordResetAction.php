<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Timebox;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetRequestCredentials;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetRequestResult;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetLinkSent;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetRequested;
use Modules\ClassicAuth\Events\Security\TooManyFailedAttempts;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle password reset request action.
 *
 * This action encapsulates:
 * - Password reset link generation
 * - Rate limiting (IP and email-based)
 * - Email sending
 * - Timing attack prevention
 */
final class RequestPasswordResetAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 900; // 15 minutes

    public function __construct(
        private readonly Timebox $timebox
    ) {}

    /**
     * Execute the password reset request action.
     *
     * @throws TooManyRequestsException
     */
    public function execute(PasswordResetRequestCredentials $credentials): PasswordResetRequestResult
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        // Dispatch request event
        event(new PasswordResetRequested(
            $credentials->email,
            $ipAddress,
            $userAgent
        ));

        // Check rate limiting
        $this->checkRateLimit($credentials);

        // Use timebox to prevent timing attacks
        $minimumMicroseconds = config('classicauth.security.auth_min_time_ms', 300) * 1000;

        return $this->timebox->call(function (Timebox $timebox) use ($credentials, $ipAddress) {
            // Send password reset link
            $status = Password::sendResetLink(
                $credentials->toArray()
            );

            // Always return success to prevent email enumeration
            // The actual status is logged but not exposed to the user
            $actuallySent = false;
            if ($status === Password::RESET_LINK_SENT) {
                logger()->info('Password reset link sent', ['email' => $credentials->email]);
                $actuallySent = true;
            } else {
                logger()->warning('Password reset link failed', [
                    'email' => $credentials->email,
                    'status' => $status,
                ]);
            }

            // Dispatch link sent event (always, to prevent enumeration)
            event(new PasswordResetLinkSent(
                $credentials->email,
                $ipAddress,
                $actuallySent
            ));

            // Allow early return for better UX
            $timebox->returnEarly();

            // Clear rate limiter on success
            $this->clearRateLimiter();
            $this->clearEmailRateLimiter($credentials->email, 'password-reset');

            // Always return success to prevent email enumeration
            return PasswordResetRequestResult::success($credentials->email);
        }, $minimumMicroseconds);
    }

    /**
     * Check rate limiting.
     *
     * @throws TooManyRequestsException
     */
    private function checkRateLimit(PasswordResetRequestCredentials $credentials): void
    {
        // IP-based rate limiting
        $maxAttempts = config('classicauth.rate_limiting.password_reset.max_attempts', self::MAX_ATTEMPTS);
        $decaySeconds = config('classicauth.rate_limiting.password_reset.decay_seconds', self::DECAY_SECONDS);

        try {
            $this->rateLimit($maxAttempts, $decaySeconds);
        } catch (TooManyRequestsException $e) {
            logger()->warning('Password reset rate limited by IP', [
                'email' => $credentials->email,
                'ip' => request()->ip(),
            ]);

            // Dispatch security event
            event(new TooManyFailedAttempts(
                'password_reset',
                request()->ip() ?? 'unknown',
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));

            throw $e;
        }

        // Email-based rate limiting
        try {
            $this->rateLimitByEmail(
                $maxAttempts,
                $this->longDuration(15, $decaySeconds),
                $credentials->email,
                'password-reset'
            );
        } catch (TooManyRequestsException $e) {
            logger()->warning('Password reset rate limited by email', [
                'email' => $credentials->email,
            ]);

            // Dispatch security event
            event(new TooManyFailedAttempts(
                'password_reset',
                $credentials->email,
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));

            throw $e;
        }
    }
}
