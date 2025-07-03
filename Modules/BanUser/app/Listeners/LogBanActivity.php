<?php

declare(strict_types=1);

namespace Modules\BanUser\Listeners;

use Modules\BanUser\Events\UserBanned;
use Illuminate\Support\Facades\Log;

/**
 * Log ban activities.
 */
final class LogBanActivity
{
    /**
     * Handle the event.
     */
    public function handle(UserBanned $event): void
    {
        Log::warning('User banned', [
            'ban_id' => $event->ban->id,
            'email' => $event->ban->email,
            'user_id' => $event->ban->user_id,
            'reason' => $event->ban->reason,
            'banned_by' => $event->ban->banned_by,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'expires_at' => $event->ban->expires_at?->toIso8601String(),
        ]);
    }
}
