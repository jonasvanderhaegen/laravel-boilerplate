<?php

declare(strict_types=1);

namespace Modules\BanUser\Events;

use Modules\BanUser\Models\BannedUser;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a banned user attempts to access the system.
 */
final class BannedUserAttempted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $action, // login, register, password_reset
        public readonly string $email,
        public readonly string $ipAddress,
        public readonly string $userAgent,
        public readonly ?BannedUser $ban = null
    ) {}
}
