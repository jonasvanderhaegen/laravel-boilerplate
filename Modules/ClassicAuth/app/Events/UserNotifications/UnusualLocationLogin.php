<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\UserNotifications;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when login is detected from an unusual location.
 */
final class UnusualLocationLogin
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user,
        public string $ipAddress,
        public string $userAgent,
        public ?string $currentLocation = null,
        public ?string $lastKnownLocation = null,
        public ?float $distance = null
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
            'current_location' => $this->currentLocation,
            'last_known_location' => $this->lastKnownLocation,
            'distance' => $this->distance,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if the location change is significant.
     */
    public function isSignificantChange(): bool
    {
        // Consider it significant if over 100km
        return $this->distance !== null && $this->distance > 100;
    }

    /**
     * Get a user-friendly location description.
     */
    public function getLocationDescription(): string
    {
        if (! $this->currentLocation) {
            return 'Unknown Location';
        }

        if ($this->lastKnownLocation && $this->distance) {
            return sprintf(
                '%s (%.0f km from your last login in %s)',
                $this->currentLocation,
                $this->distance,
                $this->lastKnownLocation
            );
        }

        return $this->currentLocation;
    }
}
