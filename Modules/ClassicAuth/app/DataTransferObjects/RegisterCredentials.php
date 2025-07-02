<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

/**
 * Data Transfer Object for registration credentials.
 *
 * Provides type-safe access to registration data with validation.
 */
final readonly class RegisterCredentials
{
    public string $email;

    public string $name;

    public function __construct(
        string $name,
        string $email,
        public string $password,
        public bool $remember = false,
    ) {
        // Name should be trimmed
        $this->name = mb_trim($name);
        // Email should be lowercase and trimmed
        $this->email = mb_strtolower(mb_trim($email));
    }

    /**
     * Create from array data.
     *
     * @param  array{name: string, email: string, password: string, remember?: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
            remember: $data['remember'] ?? false,
        );
    }

    /**
     * Convert to array for user creation.
     *
     * @return array{name: string, email: string, password: string}
     */
    public function toCreateArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    /**
     * Get credentials as array.
     *
     * @return array{name: string, email: string, password: string, remember: bool}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'remember' => $this->remember,
        ];
    }
}
