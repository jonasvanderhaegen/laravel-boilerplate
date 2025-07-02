<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\ClassicAuth\Models\LoginAttempt;

final class AuthMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:metrics 
                            {--period=today : Period to show (today, yesterday, week, month)}
                            {--type=summary : Type of metrics (summary, detailed, failures)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display authentication metrics and statistics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $period = $this->option('period');
        $type = $this->option('type');

        $this->info('Authentication Metrics');
        $this->info('======================');
        $this->newLine();

        switch ($type) {
            case 'summary':
                $this->showSummaryMetrics($period);
                break;
            case 'detailed':
                $this->showDetailedMetrics($period);
                break;
            case 'failures':
                $this->showFailureMetrics($period);
                break;
            default:
                $this->error('Invalid type. Use: summary, detailed, or failures');

                return 1;
        }

        return 0;
    }

    /**
     * Show summary metrics.
     */
    protected function showSummaryMetrics(string $period): void
    {
        $dates = $this->getPeriodDates($period);

        // Get metrics from cache
        $date = now()->format('Y-m-d');
        $successfulLogins = Cache::get("auth:metrics:daily:logins:successful:{$date}", 0);
        $failedLogins = Cache::get("auth:metrics:daily:logins:failed:{$date}", 0);
        $registrations = Cache::get("auth:metrics:daily:registrations:{$date}", 0);
        $passwordResets = Cache::get("auth:metrics:daily:password_resets:{$date}", 0);

        // Get unique users
        $uniqueUsers = count(Cache::get("auth:metrics:unique_users:{$date}", []));

        $this->table(
            ['Metric', 'Count'],
            [
                ['Successful Logins', $successfulLogins],
                ['Failed Logins', $failedLogins],
                ['New Registrations', $registrations],
                ['Password Resets', $passwordResets],
                ['Unique Users', $uniqueUsers],
                ['Success Rate', $this->calculateSuccessRate($successfulLogins, $failedLogins).'%'],
            ]
        );
    }

    /**
     * Show detailed metrics.
     */
    protected function showDetailedMetrics(string $period): void
    {
        $dates = $this->getPeriodDates($period);

        // Get hourly breakdown for today
        $hourlyData = [];
        for ($hour = 0; $hour < 24; ++$hour) {
            $hourKey = now()->format('Y-m-d:').mb_str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
            $successful = Cache::get("auth:metrics:hourly:logins:successful:{$hourKey}", 0);
            $failed = Cache::get("auth:metrics:hourly:logins:failed:{$hourKey}", 0);

            if ($successful > 0 || $failed > 0) {
                $hourlyData[] = [
                    'Hour' => mb_str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':00',
                    'Successful' => $successful,
                    'Failed' => $failed,
                    'Total' => $successful + $failed,
                ];
            }
        }

        if (empty($hourlyData)) {
            $this->info('No hourly data available for the selected period.');
        } else {
            $this->info('Hourly Breakdown:');
            $this->table(['Hour', 'Successful', 'Failed', 'Total'], $hourlyData);
        }

        // Show top failure reasons
        $this->newLine();
        $this->showTopFailureReasons();
    }

    /**
     * Show failure metrics.
     */
    protected function showFailureMetrics(string $period): void
    {
        $dates = $this->getPeriodDates($period);

        // Get failure reasons from database
        $failures = LoginAttempt::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->where('successful', false)
            ->select('failure_reason', DB::raw('count(*) as count'))
            ->groupBy('failure_reason')
            ->orderBy('count', 'desc')
            ->get();

        if ($failures->isEmpty()) {
            $this->info('No failures found for the selected period.');

            return;
        }

        $this->info('Failure Analysis:');
        $this->table(
            ['Reason', 'Count', 'Percentage'],
            $failures->map(function ($failure) use ($failures) {
                $total = $failures->sum('count');
                $percentage = round(($failure->count / $total) * 100, 1);

                return [
                    'Reason' => $this->formatFailureReason($failure->failure_reason),
                    'Count' => $failure->count,
                    'Percentage' => $percentage.'%',
                ];
            })
        );

        // Show suspicious IPs
        $this->newLine();
        $this->showSuspiciousIPs($dates);
    }

    /**
     * Show top failure reasons.
     */
    protected function showTopFailureReasons(): void
    {
        $date = now()->format('Y-m-d');
        $invalidCreds = Cache::get("auth:metrics:daily:logins:failed:invalid_credentials:{$date}", 0);
        $rateLimited = Cache::get("auth:metrics:daily:logins:failed:rate_limited:{$date}", 0);

        $this->info('Top Failure Reasons:');
        $this->table(
            ['Reason', 'Count'],
            [
                ['Invalid Credentials', $invalidCreds],
                ['Rate Limited', $rateLimited],
            ]
        );
    }

    /**
     * Show suspicious IPs.
     */
    protected function showSuspiciousIPs(array $dates): void
    {
        $suspiciousIPs = LoginAttempt::whereBetween('created_at', [$dates['start'], $dates['end']])
            ->where('successful', false)
            ->select('ip_address', DB::raw('count(*) as failed_count'))
            ->groupBy('ip_address')
            ->having('failed_count', '>', 5)
            ->orderBy('failed_count', 'desc')
            ->limit(10)
            ->get();

        if ($suspiciousIPs->isEmpty()) {
            return;
        }

        $this->info('Suspicious IPs (>5 failures):');
        $this->table(
            ['IP Address', 'Failed Attempts'],
            $suspiciousIPs->map(fn ($ip) => [$ip->ip_address, $ip->failed_count])
        );
    }

    /**
     * Get period dates.
     *
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon}
     */
    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'yesterday' => [
                'start' => now()->subDay()->startOfDay(),
                'end' => now()->subDay()->endOfDay(),
            ],
            'week' => [
                'start' => now()->subWeek()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            'month' => [
                'start' => now()->subMonth()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
            default => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
            ],
        };
    }

    /**
     * Calculate success rate.
     */
    protected function calculateSuccessRate(int $successful, int $failed): float
    {
        $total = $successful + $failed;

        if ($total === 0) {
            return 0;
        }

        return round(($successful / $total) * 100, 1);
    }

    /**
     * Format failure reason for display.
     */
    protected function formatFailureReason(?string $reason): string
    {
        return match ($reason) {
            'invalid_credentials' => 'Invalid Credentials',
            'rate_limited' => 'Rate Limited',
            'account_disabled' => 'Account Disabled',
            'account_locked' => 'Account Locked',
            null => 'Unknown',
            default => ucwords(str_replace('_', ' ', $reason)),
        };
    }
}
