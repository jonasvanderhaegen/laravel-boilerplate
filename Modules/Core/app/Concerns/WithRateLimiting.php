<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Modules\Core\Exceptions\TooManyRequestsException;

trait WithRateLimiting
{
    // Optional: Livewire components can use this as a public property
    public int $secondsUntilReset = 0;

    /**
     * Check rate limit status for a specific key.
     * Returns seconds until available, or 0 if not rate limited.
     */
    public function checkRateLimitStatus(string $key): int
    {
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return RateLimiter::availableIn($key);
        }

        return 0;
    }

    /**
     * Initialize rate limit countdown for components that need it.
     * This checks both IP and email-based rate limiting.
     */
    public function initRateLimitCountdown(
        ?string $method = null,
        ?string $component = null,
        ?string $emailKey = null
    ): void {
        // First check email-based rate limiting if we have a stored email
        if ($emailKey && session()->has($emailKey)) {
            $email = session($emailKey);
            $emailRateLimitKey = $this->getEmailRateLimitKey($email, $emailKey);

            $seconds = $this->checkRateLimitStatus($emailRateLimitKey);
            if ($seconds > 0) {
                $this->secondsUntilReset = $seconds;

                return;
            }
        }

        // Then check IP-based rate limiting
        $ipKey = $this->getRateLimitKey($method, $component);
        $this->secondsUntilReset = $this->checkRateLimitStatus($ipKey);
    }

    /**
     * Clear rate limit counters.
     */
    public function clearRateLimiter(?string $method = null, ?string $component = null): void
    {
        $method ??= $this->getCallingMethod();
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);
        RateLimiter::clear($key);
    }

    /**
     * Clear email-based rate limiter.
     */
    public function clearEmailRateLimiter(string $email, string $prefix = 'login'): void
    {
        $key = $this->getEmailRateLimitKey($email, $prefix);
        RateLimiter::clear($key);

        // Also clear from session
        session()->forget("{$prefix}_email");
    }

    /**
     * Throttle by email with better key management.
     *
     * @throws TooManyRequestsException
     */
    protected function rateLimitByEmail(
        int $maxAttempts,
        int $decaySeconds,
        string $email,
        string $prefix = 'login'
    ): void {
        $key = $this->getEmailRateLimitKey($email, $prefix);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            // Store in session for later retrieval
            session(["{$prefix}_email" => $email]);

            throw new TooManyRequestsException(
                static::class,
                'rateLimitByEmail',
                request()->ip() ?? 'unknown',
                $seconds
            );
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * General-purpose rate limiting.
     *
     * @throws TooManyRequestsException
     */
    protected function rateLimit(
        int $maxAttempts,
        int $decaySeconds = 60,
        ?string $method = null,
        ?string $component = null
    ): void {
        $method ??= $this->getCallingMethod();
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $secondsUntilAvailable = RateLimiter::availableIn($key);

            throw new TooManyRequestsException(
                $component,
                $method,
                request()->ip() ?? 'unknown',
                $secondsUntilAvailable
            );
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Hit (increment) the rate limiter.
     */
    protected function hitRateLimiter(?string $method = null, int $decaySeconds = 60, ?string $component = null): void
    {
        $method ??= $this->getCallingMethod();
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);
        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Build rate limit key for method/component/IP combination.
     */
    protected function getRateLimitKey(?string $method = null, ?string $component = null): string
    {
        $method ??= $this->getCallingMethod();
        $component ??= static::class;
        $ip = request()->ip() ?? 'unknown';

        return "rate-limiter:{$component}:{$method}:{$ip}";
    }

    /**
     * Build rate limit key for email-based limiting.
     */
    protected function getEmailRateLimitKey(string $email, string $prefix = 'login'): string
    {
        return "{$prefix}_email:".mb_strtolower($email);
    }

    /**
     * Get the calling method name using backtrace.
     */
    private function getCallingMethod(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        return $trace[2]['function'] ?? 'unknown';
    }

    /**
     * Get seconds until rate limit resets.
     */
    private function secondsUntilReset(?string $method = null, ?string $component = null): int
    {
        $key = $this->getRateLimitKey($method, $component);

        return RateLimiter::availableIn($key);
    }
}
