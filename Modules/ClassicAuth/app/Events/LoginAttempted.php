<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\ClassicAuth\Models\LoginAttempt;

/**
 * Event fired when a login attempt is made.
 */
final class LoginAttempted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public LoginAttempt $attempt
    ) {}

    /**
     * Get the login attempt.
     */
    public function getAttempt(): LoginAttempt
    {
        return $this->attempt;
    }

    /**
     * Check if the attempt was successful.
     */
    public function wasSuccessful(): bool
    {
        return $this->attempt->successful;
    }

    /**
     * Check if the attempt failed.
     */
    public function wasFailed(): bool
    {
        return ! $this->attempt->successful;
    }

    /**
     * Get the failure reason if applicable.
     */
    public function getFailureReason(): ?string
    {
        return $this->attempt->failure_reason;
    }
}
