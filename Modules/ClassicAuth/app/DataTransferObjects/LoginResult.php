<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\DataTransferObjects;

use DateTimeImmutable;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Data Transfer Object for login result.
 *
 * Encapsulates the result of a login attempt with additional context.
 */
final readonly class LoginResult
{
    public function __construct(
        public Authenticatable $user,
        public string $intendedUrl,
        public string $ipAddress,
        public string $userAgent,
        public DateTimeImmutable $authenticatedAt,
        public bool $wasRemembered = false,
    ) {}

    /**
     * Create a successful login result.
     */
    public static function success(
        Authenticatable $user,
        string $intendedUrl,
        bool $wasRemembered = false
    ): self {
        return new self(
            user: $user,
            intendedUrl: $intendedUrl,
            ipAddress: request()->ip() ?? 'unknown',
            userAgent: request()->userAgent() ?? 'unknown',
            authenticatedAt: new DateTimeImmutable(),
            wasRemembered: $wasRemembered,
        );
    }

    /**
     * Get session data for storage.
     *
     * @return array<string, mixed>
     */
    public function getSessionData(): array
    {
        return [
            'login_ip' => $this->ipAddress,
            'login_user_agent' => $this->userAgent,
            'login_at' => $this->authenticatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert to array for logging or API responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user->getAuthIdentifier(),
            'user_email' => $this->user->email,
            'intended_url' => $this->intendedUrl,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'authenticated_at' => $this->authenticatedAt->format('Y-m-d H:i:s'),
            'was_remembered' => $this->wasRemembered,
        ];
    }
}
