<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\ClassicAuth\Events\UserNotifications\NewDeviceLogin;
use Modules\ClassicAuth\Events\UserNotifications\UnusualLocationLogin;

/**
 * Send security alert emails to users.
 */
final class SendSecurityAlertEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle new device login.
     */
    public function handleNewDevice(NewDeviceLogin $event): void
    {
        // TODO: Create and send security alert email
        // Uncomment when you have the mailable:
        /*
        Mail::to($event->user->email)
            ->send(new NewDeviceAlert($event));
        */

        logger()->info('New device security alert would be sent', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'device' => $event->getDeviceDescription(),
            'location' => $event->location,
        ]);
    }

    /**
     * Handle unusual location login.
     */
    public function handleUnusualLocation(UnusualLocationLogin $event): void
    {
        // Only send for significant changes
        if (! $event->isSignificantChange()) {
            return;
        }

        // TODO: Create and send security alert email
        // Uncomment when you have the mailable:
        /*
        Mail::to($event->user->email)
            ->send(new UnusualLocationAlert($event));
        */

        logger()->info('Unusual location security alert would be sent', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'location' => $event->getLocationDescription(),
        ]);
    }

    /**
     * Subscribe to multiple events.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array<string, string>
     */
    public function subscribe($events): array
    {
        return [
            NewDeviceLogin::class => 'handleNewDevice',
            UnusualLocationLogin::class => 'handleUnusualLocation',
        ];
    }
}
