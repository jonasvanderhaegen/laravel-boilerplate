<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\UserNotifications;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when login is detected from a new device.
 */
final class NewDeviceLogin
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public string $ipAddress,
        public string $userAgent,
        public ?string $location = null,
        public ?string $device = null
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
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'location' => $this->location,
            'device' => $this->device,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get a user-friendly device description.
     */
    public function getDeviceDescription(): string
    {
        if ($this->device) {
            return $this->device;
        }

        // Parse user agent for device info
        $agent = mb_strtolower($this->userAgent);

        if (str_contains($agent, 'mobile')) {
            return 'Mobile Device';
        }
        if (str_contains($agent, 'tablet')) {
            return 'Tablet';
        }

        return 'Desktop Computer';

    }
}
