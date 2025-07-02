<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

/**
 * Data Transfer Object for password reset credentials.
 *
 * Provides type-safe access to password reset data.
 */
final readonly class PasswordResetCredentials
{
    public string $email;

    public function __construct(
        string $email,
        public string $token,
        public string $password
    ) {
        // Email should be lowercase and trimmed
        $this->email = mb_strtolower(mb_trim($email));
    }

    /**
     * Create from array data.
     *
     * @param  array{email: string, token: string, password: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            token: $data['token'] ?? '',
            password: $data['password'] ?? ''
        );
    }

    /**
     * Convert to array for password reset.
     *
     * @return array{email: string, token: string, password: string, password_confirmation: string}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ];
    }
}
