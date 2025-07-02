<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\PasswordReset;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a password reset link is sent.
 * Note: Always fires regardless of whether email exists to prevent enumeration.
 */
final class PasswordResetLinkSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $email,
        public string $ipAddress,
        public bool $actuallySent
    ) {}

    /**
     * Get event data for logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'ip_address' => $this->ipAddress,
            'actually_sent' => $this->actuallySent,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
