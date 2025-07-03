<?php

declare(strict_types=1);

namespace Modules\BanUser\Listeners;

use Modules\ClassicAuth\Events\Registration\UserRegistering;
use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Events\BannedUserAttempted;
use Illuminate\Validation\ValidationException;

/**
 * Check for bans when user registration is attempted.
 */
final class CheckBanOnRegistration
{
    public function __construct(
        private readonly BanCheckService $banCheckService
    ) {}

    /**
     * Handle the event.
     *
     * @throws ValidationException
     */
    public function handle(UserRegistering $event): void
    {
        $email = $event->credentials->email;
        $ipAddress = $event->ipAddress;
        $userAgent = $event->userAgent;

        if ($this->banCheckService->areCredentialsBanned($email, $ipAddress)) {
            $ban = $this->banCheckService->getBanDetails($email);
            
            // Dispatch event
            event(new BannedUserAttempted(
                'register',
                $email,
                $ipAddress,
                $userAgent,
                $ban
            ));

            throw ValidationException::withMessages([
                'email' => 'Registration is not allowed with this email address.',
            ]);
        }
    }
}
