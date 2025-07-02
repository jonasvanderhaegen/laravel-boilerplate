<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Timebox;
use Modules\ClassicAuth\Events\EmailVerification\EmailVerificationLinkSent;
use Modules\ClassicAuth\Events\EmailVerification\EmailVerificationRequested;
use Modules\ClassicAuth\Events\Security\TooManyFailedAttempts;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle email verification resend action.
 *
 * This action encapsulates:
 * - Email verification link resending
 * - Rate limiting
 * - Timing attack prevention
 */
final class ResendVerificationEmailAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 3;

    private const DECAY_SECONDS = 300; // 5 minutes

    public function __construct(
        private readonly Timebox $timebox
    ) {}

    /**
     * Execute the email verification resend action.
     *
     * @throws TooManyRequestsException
     */
    public function execute(): bool
    {
        $user = Auth::user();
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        if (! $user instanceof User) {
            return false;
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        // Dispatch request event
        event(new EmailVerificationRequested($user, $ipAddress, $userAgent));

        // Check rate limiting
        $this->checkRateLimit($user);

        // Use timebox to prevent timing attacks
        $minimumMicroseconds = config('classicauth.security.auth_min_time_ms', 300) * 1000;

        return $this->timebox->call(function (Timebox $timebox) use ($user, $ipAddress) {
            // Send verification email
            $user->sendEmailVerificationNotification();

            // Dispatch link sent event
            event(new EmailVerificationLinkSent($user, $ipAddress));

            // Allow early return for better UX
            $timebox->returnEarly();

            // Clear rate limiter
            $this->clearRateLimiter();

            // Log the action
            logger()->info('Email verification resent', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return true;
        }, $minimumMicroseconds);
    }

    /**
     * Check rate limiting.
     *
     * @throws TooManyRequestsException
     */
    private function checkRateLimit(User $user): void
    {
        $maxAttempts = config('classicauth.rate_limiting.email_verification.max_attempts', self::MAX_ATTEMPTS);
        $decaySeconds = config('classicauth.rate_limiting.email_verification.decay_seconds', self::DECAY_SECONDS);

        try {
            $this->rateLimit($maxAttempts, $decaySeconds);
        } catch (TooManyRequestsException $e) {
            logger()->warning('Email verification resend rate limited', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => request()->ip(),
            ]);

            // Dispatch security event
            event(new TooManyFailedAttempts(
                'email_verification',
                request()->ip() ?? 'unknown',
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));

            throw $e;
        }
    }
}
