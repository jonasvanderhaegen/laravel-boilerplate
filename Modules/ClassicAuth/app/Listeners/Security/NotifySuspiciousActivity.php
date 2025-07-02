<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners\Security;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\ClassicAuth\Events\Security\SuspiciousActivityDetected;

/**
 * Handle suspicious activity notifications.
 */
final class NotifySuspiciousActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SuspiciousActivityDetected $event): void
    {
        // Log the suspicious activity with high priority
        Log::warning('Suspicious activity detected', $event->toArray());

        // Set cache flag for middleware enforcement
        if ($event->email) {
            // Try to find user by email
            $user = \App\Models\User::where('email', $event->email)->first();
            if ($user) {
                Cache::put(
                    "user:{$user->id}:suspicious_activity",
                    [
                        'type' => $event->activityType,
                        'timestamp' => now()->timestamp,
                        'ip' => $event->ipAddress,
                    ],
                    now()->addHours(6)
                );

                // For severe cases, flag for additional verification
                if (in_array($event->activityType, ['brute_force', 'credential_stuffing'])) {
                    Cache::put(
                        "user:{$user->id}:requires_2fa",
                        true,
                        now()->addDays(7)
                    );
                }
            }
        }

        // TODO: Implement email notification to administrators
        // You can uncomment and configure this based on your needs:
        /*
        if (config('classicauth.notifications.suspicious_activity_email')) {
            Mail::to(config('classicauth.notifications.admin_email'))
                ->send(new SuspiciousActivityAlert($event));
        }
        */

        // TODO: Implement additional security measures
        // - Block IP address after threshold
        // - Temporarily lock account
        // - Require additional verification
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(SuspiciousActivityDetected $event): bool
    {
        // Queue for non-critical suspicious activities
        return ! in_array($event->activityType, ['brute_force', 'credential_stuffing']);
    }
}
