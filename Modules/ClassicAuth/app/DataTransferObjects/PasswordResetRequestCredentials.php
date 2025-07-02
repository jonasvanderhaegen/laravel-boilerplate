<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

/**
 * Data Transfer Object for password reset request credentials.
 *
 * Provides type-safe access to password reset request data.
 */
final readonly class PasswordResetRequestCredentials
{
    public string $email;

    public function __construct(
        string $email
    ) {
        // Email should be lowercase and trimmed
        $this->email = mb_strtolower(mb_trim($email));
    }

    /**
     * Create from array data.
     *
     * @param  array{email: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? ''
        );
    }

    /**
     * Convert to array.
     *
     * @return array{email: string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
