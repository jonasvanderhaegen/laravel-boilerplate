<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\ClassicAuth\Events\EmailVerification\EmailVerificationCompleted;
use Modules\ClassicAuth\Events\Security\TooManyFailedAttempts;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle email verification confirmation action.
 *
 * This action encapsulates:
 * - Email verification processing
 * - Rate limiting
 * - Event dispatching
 */
final class VerifyEmailAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 300; // 5 minutes

    /**
     * Execute the email verification action.
     *
     * @throws TooManyRequestsException
     */
    public function execute(int $id, string $hash): bool
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';
        
        // Check rate limiting
        $this->checkRateLimit();

        // Find the user
        $user = User::find($id);

        if (!$user) {
            logger()->warning('Email verification attempted for non-existent user', ['id' => $id]);
            return false;
        }

        // Check if the hash matches
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            logger()->warning('Email verification attempted with invalid hash', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return false;
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return true;
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            
            // Dispatch our custom event
            event(new EmailVerificationCompleted($user, $ipAddress, $userAgent));
            
            logger()->info('Email verified successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Clear rate limiter
            $this->clearRateLimiter();

            return true;
        }

        return false;
    }

    /**
     * Check rate limiting.
     *
     * @throws TooManyRequestsException
     */
    private function checkRateLimit(): void
    {
        $maxAttempts = config('classicauth.rate_limiting.email_verification.max_attempts', self::MAX_ATTEMPTS);
        $decaySeconds = config('classicauth.rate_limiting.email_verification.decay_seconds', self::DECAY_SECONDS);

        try {
            $this->rateLimit($maxAttempts, $decaySeconds);
        } catch (TooManyRequestsException $e) {
            logger()->warning('Email verification rate limited', [
                'ip' => request()->ip()
            ]);
            
            // Dispatch security event
            event(new TooManyFailedAttempts(
                'email_verification_confirm',
                request()->ip() ?? 'unknown',
                $maxAttempts,
                $decaySeconds,
                request()->ip() ?? 'unknown'
            ));
            
            throw $e;
        }
    }
}
