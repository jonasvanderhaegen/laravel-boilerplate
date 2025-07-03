<?php

declare(strict_types=1);

namespace Modules\BanUser\Listeners;

use Modules\BanUser\Events\BannedUserAttempted;
use Illuminate\Support\Facades\Log;

/**
 * Log and notify when banned users attempt to access.
 */
final class NotifyBannedUserAttempt
{
    /**
     * Handle the event.
     */
    public function handle(BannedUserAttempted $event): void
    {
        Log::warning('Banned user attempted access', [
            'action' => $event->action,
            'email' => $event->email,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'ban_id' => $event->ban?->id,
            'ban_reason' => $event->ban?->reason,
        ]);

        // Here you could add additional notifications:
        // - Send email to admin
        // - Send to monitoring service
        // - Increment security counter
        // - Trigger additional security measures
    }
}
