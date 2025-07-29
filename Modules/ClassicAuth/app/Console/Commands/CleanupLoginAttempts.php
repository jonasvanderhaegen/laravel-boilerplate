<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Console\Commands;

use Illuminate\Console\Command;
use Modules\ClassicAuth\Models\LoginAttempt;

/**
 * Clean up old login attempt records.
 */
final class CleanupLoginAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'classicauth:cleanup-login-attempts 
                            {--days= : Number of days to retain (overrides config)}
                            {--dry-run : Show what would be deleted without deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old login attempt records based on retention policy';

    /**
     * Execute the console command.
     */
    // @codeCoverageIgnoreStart
    public function handle(): int
    {
        $retentionDays = $this->option('days')
            ?? config('classicauth.tracking.retention_days');

        if (! $retentionDays) {
            $this->info('Login attempt cleanup is disabled (retention_days is not set).');

            return self::SUCCESS;
        }

        $cutoffDate = now()->subDays($retentionDays);

        $query = LoginAttempt::where('attempted_at', '<', $cutoffDate);
        $count = $query->count();

        if ($count === 0) {
            $this->info('No login attempts to clean up.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} login attempts older than {$retentionDays} days.");

            // Show sample of what would be deleted
            $sample = $query->limit(5)->get();
            $this->table(
                ['ID', 'Email', 'IP', 'Status', 'Attempted At'],
                $sample->map(fn ($attempt) => [
                    $attempt->id,
                    $attempt->email,
                    $attempt->ip_address,
                    $attempt->successful ? 'Success' : 'Failed',
                    $attempt->attempted_at->format('Y-m-d H:i:s'),
                ])->toArray()
            );

            if ($count > 5) {
                $this->line('... and '.($count - 5).' more records.');
            }

            return self::SUCCESS;
        }

        $this->info("Deleting {$count} login attempts older than {$retentionDays} days...");

        $deleted = $query->delete();

        $this->info("Successfully deleted {$deleted} login attempt records.");

        return self::SUCCESS;
    }
    // @codeCoverageIgnoreEnd
}
