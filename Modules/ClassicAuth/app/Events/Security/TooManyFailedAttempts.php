<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Security;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when too many failed attempts are detected.
 */
final class TooManyFailedAttempts
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $attemptType,
        public string $identifier, // Email or IP
        public int $attemptCount,
        public int $timeWindow,
        public string $ipAddress
    ) {}

    /**
     * Get event data for logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'attempt_type' => $this->attemptType,
            'identifier' => $this->identifier,
            'attempt_count' => $this->attemptCount,
            'time_window' => $this->timeWindow,
            'ip_address' => $this->ipAddress,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get a human-readable description.
     */
    public function getDescription(): string
    {
        return sprintf(
            'Too many %s attempts (%d) from %s within %d seconds',
            $this->attemptType,
            $this->attemptCount,
            $this->identifier,
            $this->timeWindow
        );
    }
}
