<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

/**
 * Data Transfer Object for password reset request result.
 *
 * Provides structured response after password reset request attempt.
 */
final readonly class PasswordResetRequestResult
{
    private function __construct(
        public bool $success,
        public string $email,
        public ?string $message = null,
    ) {}

    /**
     * Create a successful result.
     *
     * Always returns success to prevent email enumeration.
     */
    public static function success(string $email): self
    {
        return new self(
            success: true,
            email: $email,
            message: __('passwords.sent'),
        );
    }

    /**
     * Convert to array.
     *
     * @return array{success: bool, email: string, message: string|null}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'email' => $this->email,
            'message' => $this->message,
        ];
    }

    /**
     * Get session data to store.
     *
     * @return array<string, mixed>
     */
    public function getSessionData(): array
    {
        return [
            'password_reset.requested' => true,
            'password_reset.email' => $this->email,
            'password_reset.timestamp' => now()->timestamp,
        ];
    }
}
