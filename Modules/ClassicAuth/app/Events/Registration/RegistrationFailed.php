<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events\Registration;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when registration fails.
 */
final class RegistrationFailed
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
            'user_agent' => $this->userAgent,
            'failure_reason' => $this->failureReason,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Check if failure was due to duplicate email.
     */
    public function isDuplicateEmail(): bool
    {
        return $this->failureReason === 'email_taken';
    }

    /**
     * Check if failure was due to rate limiting.
     */
    public function isRateLimited(): bool
    {
        return $this->failureReason === 'rate_limited';
    }
}
