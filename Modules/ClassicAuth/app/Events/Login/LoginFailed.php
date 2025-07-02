<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Login;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a login attempt fails.
 */
final class LoginFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $email,
        public string $ipAddress,
        public string $userAgent,
        public string $failureReason
    ) {
        // Ray: Log login failed event
        ray()
            ->label('🔴 Login Failed Event')
            ->table([
                'Email' => $this->email,
                'IP Address' => $this->ipAddress,
                'User Agent' => mb_substr($this->userAgent, 0, 50).'...',
                'Failure Reason' => $this->failureReason,
                'Timestamp' => now()->toDateTimeString(),
            ])
            ->color('red');
    }

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
            'user_agent' => $this->userAgent,
            'failure_reason' => $this->failureReason,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if failure was due to invalid credentials.
     */
    public function isInvalidCredentials(): bool
    {
        return $this->failureReason === 'invalid_credentials';
    }

    /**
     * Check if failure was due to rate limiting.
     */
    public function isRateLimited(): bool
    {
        return $this->failureReason === 'rate_limited';
    }
}
