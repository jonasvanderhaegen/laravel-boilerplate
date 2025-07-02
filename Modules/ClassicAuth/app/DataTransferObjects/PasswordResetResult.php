<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

/**
 * Data Transfer Object for password reset result.
 *
 * Provides structured response after password reset attempt.
 */
final readonly class PasswordResetResult
{
    private function __construct(
        public bool $success,
        public string $email,
        public ?string $redirectUrl = null,
        public ?string $message = null,
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(string $email, ?string $redirectUrl = null): self
    {
        return new self(
            success: true,
            email: $email,
            redirectUrl: $redirectUrl ?? route('login'),
            message: __('passwords.reset'),
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(string $email, string $message): self
    {
        return new self(
            success: false,
            email: $email,
            message: $message,
        );
    }

    /**
     * Convert to array.
     *
     * @return array{success: bool, email: string, redirect_url: string|null, message: string|null}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'email' => $this->email,
            'redirect_url' => $this->redirectUrl,
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
        if (! $this->success) {
            return [];
        }

        return [
            'password_reset.completed' => true,
            'password_reset.email' => $this->email,
            'password_reset.timestamp' => now()->timestamp,
        ];
    }
}
