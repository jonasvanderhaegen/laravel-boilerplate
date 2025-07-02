<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Security;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when suspicious activity is detected.
 */
final class SuspiciousActivityDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $activityType,
        public string $ipAddress,
        public ?string $email = null,
        public array $metadata = []
    ) {}

    /**
     * Get event data for logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'activity_type' => $this->activityType,
            'ip_address' => $this->ipAddress,
            'email' => $this->email,
            'metadata' => $this->metadata,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if this is a brute force attempt.
     */
    public function isBruteForce(): bool
    {
        return $this->activityType === 'brute_force';
    }

    /**
     * Check if this is a credential stuffing attempt.
     */
    public function isCredentialStuffing(): bool
    {
        return $this->activityType === 'credential_stuffing';
    }

    /**
     * Check if this is multiple IP addresses for same account.
     */
    public function isMultipleIPs(): bool
    {
        return $this->activityType === 'multiple_ips';
    }
}
