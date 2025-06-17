<?php

declare(strict_types=1);

namespace Modules\Core\Concerns;

// @codeCoverageIgnoreStart
trait RateLimitDurations
{
    /**
     * Return one of two values depending on whether we're in a “fast” env.
     *
     * @param  int  $fast  Value to use in local/testing
     * @param  int  $normal  Value to use otherwise
     */
    protected function envDuration(int $fast, int $normal): int
    {
        // treat local & testing as “fast” so your feedback loops are quick
        if (app()->environment(['local'])) {
            return $fast;
        }

        return $normal;
    }

    /**
     * Shorthand for your “short” throttle bucket.
     *
     * @param  string  $config  Config key for the throttle setting
     * @param  int  $short  Value to use in local/testing
     * @param  int  $long  Fallback value to use otherwise
     */
    protected function shortDuration(
        string $config = 'auth.passwords.users.throttle',
        int $short = 60,
        int $long = 60
    ): int {
        return $this->envDuration($short, config($config, $long));
    }

    /**
     * Shorthand for your “long” throttle bucket.
     *
     * @param  int  $short  Value to use in local/testing
     * @param  int  $long  Fallback value to use otherwise
     */
    protected function longDuration(int $short = 90, int $long = 900): int
    {
        return $this->envDuration($short, $long);
    }
}
// @codeCoverageIgnoreEnd
