<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

/**
 * Data Transfer Object for login credentials.
 *
 * Provides type-safe access to log-in data with validation.
 */
final readonly class LoginCredentials
{
    public string $email;

    public function __construct(
        string $email,
        public string $password,
        public bool $remember = false,
    ) {
        // Email should be lowercase and trimmed
        $this->email = mb_strtolower(mb_trim($email));
    }

    /**
     * Create from array data.
     *
     * @param  array{email: string, password: string, remember?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            remember: $data['remember'] ?? false,
        );
    }

    /**
     * Convert to array for authentication attempt.
     *
     * @return array{email: string, password: string}
     */
    public function toAuthArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    /**
     * Get credentials as array.
     *
     * @return array{email: string, password: string, remember: bool}
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ];
    }
}
