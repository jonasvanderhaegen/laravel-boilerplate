<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Modules\Core\Exceptions\TooManyRequestsException;

trait WithRateLimiting
{
    // Livewire will pick this up as a public property on the component
    public int $secondsUntilReset = 0;

    /**
     * Call this in your component's mount or constructor
     * to initialize the countdown for a given method.
     */
    public function initRateLimitCountdown(
        ?string $method = null,
        ?string $component = null,
        string|bool $auth = false
    ): void {

        if (session()->has("{$auth}_email") && $auth) {
            $email = session("{$auth}_email");
            $key = "{$auth}_email:$email";

            if (RateLimiter::availableIn($key)) {
                $this->secondsUntilReset = RateLimiter::availableIn($key);

                return;
            }
        }

        $this->secondsUntilReset = $this->secondsUntilReset($method, $component);
    }

    /**
     * Clear a Livewire‐specific rate limit counter.
     */
    public function clearRateLimiter(?string $method = null, ?string $component = null): void
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        RateLimiter::clear($key);
    }

    /**
     * Throttle by email: max $maxAttempts sends per $decaySeconds seconds.
     *
     *
     * @throws TooManyRequestsException
     */
    protected function rateLimitByEmail(
        int $maxAttempts,
        int $decaySeconds,
        string $email,
        string|bool $auth
    ): void {
        $key = "{$auth}_email:$email";

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            // Store in session so mount() / initRateLimitCountdown() can pick it up
            session([
                "{$auth}_email" => $email,
            ]);

            throw new TooManyRequestsException(
                static::class,
                'rateLimitByEmail',
                request()->ip(),
                $seconds
            );
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
    {
    /**
     * Build the unique key for a method/component/IP combination.
     */
    protected function getRateLimitKey(?string $method, ?string $component = null): string
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];
        $component ??= static::class;

        return 'livewire-rate-limiter:'.sha1($component.'|'.$method.'|'.request()->ip());
    }

    /**
     * Hit (increment) the rate limiter for a given Livewire method.
     */
    protected function hitRateLimiter(?string $method = null, int $decaySeconds = 60, ?string $component = null): void
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * General-purpose rate limit check/hit for a Livewire method.
     *
     *
     * @throws TooManyRequestsException
     */
    protected function rateLimit(
        int $maxAttempts,
        int $decaySeconds = 60,
        ?string $method = null,
        ?string $component = null
    ): void {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2)[1]['function'];
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $ip = request()->ip();
            $secondsUntilAvailable = RateLimiter::availableIn($key);

            throw new TooManyRequestsException($component, $method, $ip, $secondsUntilAvailable);
        }

        $this->hitRateLimiter($method, $decaySeconds, $component);
    }

    /**
     * How many seconds until a Livewire method is available again.
     */
    private function secondsUntilReset(?string $method = null, ?string $component = null): int
    {
        $method ??= debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        $component ??= static::class;

        $key = $this->getRateLimitKey($method, $component);

        return RateLimiter::availableIn($key);
    }
}
