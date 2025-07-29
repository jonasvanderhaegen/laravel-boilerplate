<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\ClassicAuth\Events\LoginAttempted;

/**
 * Listener for login attempt events.
 *
 * Handles additional logging, notifications, and security checks.
 */
final class LogLoginAttempt implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LoginAttempted $event): void
    {
        $attempt = $event->getAttempt();

        // Log the attempt
        Log::channel('auth')->info('Login attempt', [
            'successful' => $attempt->successful,
            'email' => $attempt->email,
            'ip_address' => $attempt->ip_address,
            'user_agent' => $attempt->user_agent,
            'failure_reason' => $attempt->failure_reason,
            'attempted_at' => $attempt->attempted_at->toIso8601String(),
        ]);

        // Check for suspicious activity
        if ($this->isSuspiciousActivity($attempt)) {
            $this->handleSuspiciousActivity($attempt);
        }

        // Notify user of login from new location (if successful)
        if ($attempt->successful && $attempt->user && $this->isNewLocation($attempt)) {
            $this->notifyUserOfNewLocation($attempt);
        }
    }

    /**
     * Check if the login attempt is suspicious.
     */
    // @codeCoverageIgnoreStart
    private function isSuspiciousActivity($attempt): bool
    {
        $config = config('classicauth.tracking.suspicious_activity');

        // Check for multiple failed attempts from same IP
        $recentFailures = $attempt->newQuery()
            ->failed()
            ->byIp($attempt->ip_address)
            ->where('attempted_at', '>=', now()->subSeconds($config['ip_failures_window'] ?? 3600))
            ->count();

        if ($recentFailures >= ($config['ip_failures_threshold'] ?? 10)) {
            return true;
        }

        // Check for multiple failed attempts for same email from different IPs
        if (! $attempt->successful) {
            $differentIps = $attempt->newQuery()
                ->failed()
                ->byEmail($attempt->email)
                ->where('attempted_at', '>=', now()->subSeconds($config['email_ips_window'] ?? 21600))
                ->distinct('ip_address')
                ->count('ip_address');

            if ($differentIps >= ($config['email_ips_threshold'] ?? 5)) {
                return true;
            }
        }

        return false;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Handle suspicious activity.
     */
    // @codeCoverageIgnoreStart
    private function handleSuspiciousActivity($attempt): void
    {
        Log::channel('security')->warning('Suspicious login activity detected', [
            'email' => $attempt->email,
            'ip_address' => $attempt->ip_address,
            'user_agent' => $attempt->user_agent,
        ]);

        // Here you could:
        // - Send security alert to admin
        // - Temporarily block IP
        // - Require additional verification
        // - Send alert to user (if email is valid)
    }
    // @codeCoverageIgnoreEnd

    /**
     * Check if login is from a new location.
     */
    // @codeCoverageIgnoreStart
    private function isNewLocation($attempt): bool
    {
        if (! $attempt->user) {
            return false;
        }

        // Check if this IP has been used before by this user
        $previousLogin = $attempt->newQuery()
            ->successful()
            ->where('user_id', $attempt->user_id)
            ->where('ip_address', $attempt->ip_address)
            ->where('id', '!=', $attempt->id)
            ->exists();

        return ! $previousLogin;
    }
    // @codeCoverageIgnoreEnd

    /**
     * Notify user of login from new location.
     */
    // @codeCoverageIgnoreStart
    private function notifyUserOfNewLocation($attempt): void
    {
        // Here you could send an email notification
        // Example:
        // Mail::to($attempt->user)->queue(new NewLocationLogin($attempt));

        Log::info('New location login detected', [
            'user_id' => $attempt->user_id,
            'email' => $attempt->email,
            'ip_address' => $attempt->ip_address,
        ]);
    }
    // @codeCoverageIgnoreEnd
}
