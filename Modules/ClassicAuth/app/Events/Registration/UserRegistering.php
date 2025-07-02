<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Registration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ClassicAuth\DataTransferObjects\RegisterCredentials;

/**
 * Event fired before user registration is processed.
 */
final class UserRegistering
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public RegisterCredentials $credentials,
        public string $ipAddress,
        public string $userAgent
    ) {}

    /**
     * Get event data for logging.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->credentials->email,
            'name' => $this->credentials->name,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
