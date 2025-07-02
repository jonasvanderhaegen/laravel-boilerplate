<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

use App\Models\User;

/**
 * Data Transfer Object for registration result.
 *
 * Provides structured response after registration attempt.
 */
final readonly class RegisterResult
{
    private function __construct(
        public bool $success,
        public ?User $user = null,
        public ?string $redirectUrl = null,
        public ?string $message = null,
    ) {}

    /**
     * Create a successful registration result.
     */
    public static function success(User $user, ?string $redirectUrl = null): self
    {
        return new self(
            success: true,
            user: $user,
            redirectUrl: $redirectUrl ?? route('dashboard'),
            message: __('auth.registration_successful'),
        );
    }

    /**
     * Create a failed registration result.
     */
    public static function failure(string $message): self
    {
        return new self(
            success: false,
            message: $message,
        );
    }

    /**
     * Convert to array.
     *
     * @return array{success: bool, user_id: int|null, redirect_url: string|null, message: string|null}
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'user_id' => $this->user?->id,
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
        if (! $this->success || ! $this->user) {
            return [];
        }

        return [
            'register.completed' => true,
            'register.user_id' => $this->user->id,
            'register.timestamp' => now()->timestamp,
        ];
    }
}
