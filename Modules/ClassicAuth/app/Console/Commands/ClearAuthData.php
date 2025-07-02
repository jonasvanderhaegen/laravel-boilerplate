<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\ClassicAuth\Models\LoginAttempt;

final class ClearAuthData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:clear 
                            {--metrics : Clear metrics cache}
                            {--attempts : Clear login attempts}
                            {--devices : Clear known devices}
                            {--all : Clear all authentication data}
                            {--older-than= : Clear data older than specified days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear authentication data (metrics, login attempts, known devices)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $clearAll = $this->option('all');
        $olderThan = $this->option('older-than');

        if (! $clearAll && ! $this->option('metrics') && ! $this->option('attempts') && ! $this->option('devices')) {
            $this->error('Please specify what to clear: --metrics, --attempts, --devices, or --all');

            return 1;
        }

        if ($clearAll || $this->option('metrics')) {
            $this->clearMetrics();
        }

        if ($clearAll || $this->option('attempts')) {
            $this->clearLoginAttempts($olderThan);
        }

        if ($clearAll || $this->option('devices')) {
            $this->clearKnownDevices();
        }

        $this->info('Authentication data cleared successfully!');

        return 0;
    }

    /**
     * Clear metrics cache.
     */
    protected function clearMetrics(): void
    {
        $this->info('Clearing metrics cache...');

        // Clear daily metrics
        $pattern = 'auth:metrics:daily:*';
        $this->clearCachePattern($pattern);

        // Clear hourly metrics
        $pattern = 'auth:metrics:hourly:*';
        $this->clearCachePattern($pattern);

        // Clear unique users
        $pattern = 'auth:metrics:unique_users:*';
        $this->clearCachePattern($pattern);

        $this->info('✓ Metrics cache cleared');
    }

    /**
     * Clear login attempts.
     */
    protected function clearLoginAttempts(?string $olderThan): void
    {
        $this->info('Clearing login attempts...');

        if ($olderThan) {
            $count = LoginAttempt::where('created_at', '<', now()->subDays((int) $olderThan))
                ->delete();
            $this->info("✓ Deleted {$count} login attempts older than {$olderThan} days");
        } else {
            if ($this->confirm('Are you sure you want to delete ALL login attempts?')) {
                $count = LoginAttempt::truncate();
                $this->info('✓ All login attempts cleared');
            }
        }
    }

    /**
     * Clear known devices.
     */
    protected function clearKnownDevices(): void
    {
        $this->info('Clearing known devices...');

        $pattern = 'user:*:known_devices';
        $this->clearCachePattern($pattern);

        // Clear login history
        $pattern = 'user:*:login_history';
        $this->clearCachePattern($pattern);

        $this->info('✓ Known devices cleared');
    }

    /**
     * Clear cache by pattern.
     */
    protected function clearCachePattern(string $pattern): void
    {
        // Note: This is a simplified version
        // In production, you'd need to use Redis SCAN or similar
        // for pattern-based deletion

        try {
            // If using Redis
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getRedis();
                $keys = $redis->keys($pattern);

                if (! empty($keys)) {
                    $redis->del($keys);
                }
            } else {
                // For other cache drivers, we can't easily clear by pattern
                $this->warn('Pattern-based clearing not supported for current cache driver');
            }
        } catch (Exception $e) {
            $this->warn("Could not clear cache pattern {$pattern}: ".$e->getMessage());
        }
    }
}
