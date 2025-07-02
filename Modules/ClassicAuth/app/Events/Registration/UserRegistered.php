<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Registration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

/**
 * Event fired after successful user registration.
 */
final class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public string $ipAddress,
        public string $userAgent,
        public bool $autoLoggedIn = false
    ) {}

    /**
     * Get event data for logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'name' => $this->user->name,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'auto_logged_in' => $this->autoLoggedIn,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
