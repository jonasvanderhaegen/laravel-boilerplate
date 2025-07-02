<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Modules\ClassicAuth\Events\Registration\UserRegistered;
use Throwable;

/**
 * Send welcome email after user registration.
 */
final class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        // TODO: Create and send welcome email
        // Uncomment and implement when you have a welcome email mailable:
        /*
        Mail::to($event->user->email)
            ->send(new WelcomeEmail($event->user));
        */

        // For now, just log that we would send a welcome email
        logger()->info('Welcome email would be sent', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, Throwable $exception): void
    {
        logger()->error('Failed to send welcome email', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
