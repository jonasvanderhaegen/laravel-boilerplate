<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Illuminate\Support\Facades\DB;
use Modules\ClassicAuth\Events\Login\LoginSucceeded;
use Modules\ClassicAuth\Models\LoginAttempt;

/**
 * Clean up old authentication records periodically.
 */
class CleanupAuthenticationData
{
    /**
     * Handle successful login to trigger cleanup.
     */
    public function handle(LoginSucceeded $event): void
    {
        // Only run cleanup 1% of the time to avoid performance impact
        if (rand(1, 100) !== 1) {
            return;
        }

        $this->cleanupOldLoginAttempts();
        $this->cleanupOldMetrics();
    }

    /**
     * Clean up old login attempts based on retention policy.
     */
    private function cleanupOldLoginAttempts(): void
    {
        $retentionDays = config('classicauth.tracking.retention_days', 90);
        
        if (!$retentionDays) {
            return; // Keep records indefinitely
        }

        try {
            $deleted = LoginAttempt::where('created_at', '<', now()->subDays($retentionDays))
                ->delete();
                
            if ($deleted > 0) {
                logger()->info('Cleaned up old login attempts', [
                    'deleted_count' => $deleted,
                    'retention_days' => $retentionDays,
                ]);
            }
        } catch (\Exception $e) {
            logger()->error('Failed to cleanup login attempts', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean up old metrics data from cache.
     */
    private function cleanupOldMetrics(): void
    {
        // This would clean up old cached metrics
        // Implementation depends on your cache driver
        // For now, just log that we would do cleanup
        
        logger()->debug('Authentication metrics cleanup would run here');
    }
}
