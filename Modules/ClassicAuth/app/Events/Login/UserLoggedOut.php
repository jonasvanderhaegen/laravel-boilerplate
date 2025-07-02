<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Login;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

/**
 * Event fired when a user logs out.
 */
final class UserLoggedOut
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ?User $user,
        public string $ipAddress,
        public string $userAgent,
        public string $sessionId
    ) {}

    /**
     * Get event data for logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user?->id,
            'email' => $this->user?->email,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'session_id' => $this->sessionId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
