<?php

declare(strict_types=1);

namespace App\Actions\Example;

use App\Events\UserRegistered;
use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Example of a pragmatic action class following the action-driven pattern.
 *
 * This action demonstrates:
 * - Business logic encapsulation
 * - Transaction handling
 * - Business rule validation
 * - Side effect management
 * - Type safety
 */
final class RegisterUserAction
{
    /**
     * Execute the user registration action.
     *
     * @param  array{name: string, email: string, password: string, terms_accepted: bool}  $data
     *
     * @throws BusinessException
     */
    public function execute(array $data): User
    {
        // Validate business rules
        $this->validateBusinessRules($data);

        return DB::transaction(function () use ($data) {
            // Create the user
            $user = $this->createUser($data);

            // Handle side effects
            $this->handlePostRegistration($user);

            return $user;
        });
    }

    /**
     * Validate business-specific rules beyond simple input validation.
     */
    private function validateBusinessRules(array $data): void
    {
        // Example: Check if registration is allowed
        if (! config('app.registration_enabled', true)) {
            throw new BusinessException('Registration is currently disabled');
        }

        // Example: Check email domain restrictions
        if ($this->isEmailDomainBlocked($data['email'])) {
            throw new BusinessException('Email domain is not allowed');
        }

        // Example: Terms must be accepted
        if (! $data['terms_accepted']) {
            throw new BusinessException('Terms and conditions must be accepted');
        }
    }

    /**
     * Create the user record.
     */
    private function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => null,
        ]);
    }

    /**
     * Handle post-registration side effects.
     */
    private function handlePostRegistration(User $user): void
    {
        // Dispatch events
        event(new UserRegistered($user));

        // Send welcome email (handled by event listener)
        // Create default user settings (handled by event listener)
        // Log registration for analytics (handled by event listener)
    }

    /**
     * Check if email domain is blocked.
     */
    private function isEmailDomainBlocked(string $email): bool
    {
        $blockedDomains = config('app.blocked_email_domains', []);
        $domain = mb_substr(mb_strrchr($email, '@'), 1);

        return in_array($domain, $blockedDomains, true);
    }
}
