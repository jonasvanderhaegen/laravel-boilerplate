<?php

declare(strict_types=1);

namespace Modules\BanUser\Events;

use Modules\BanUser\Models\BannedUser;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a user is banned.
 */
final class UserBanned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly BannedUser $ban,
        public readonly string $ipAddress,
        public readonly string $userAgent
    ) {}
}
