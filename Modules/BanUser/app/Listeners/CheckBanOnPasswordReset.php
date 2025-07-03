<?php

declare(strict_types=1);

namespace Modules\BanUser\Listeners;

use Modules\ClassicAuth\Events\PasswordReset\PasswordResetRequested;
use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Events\BannedUserAttempted;

/**
 * Check for bans when password reset is requested.
 * 
 * Note: We don't throw exceptions here to prevent email enumeration.
 * We just log the attempt.
 */
final class CheckBanOnPasswordReset
{
    public function __construct(
        private readonly BanCheckService $banCheckService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PasswordResetRequested $event): void
    {
        $email = $event->email;
        $ipAddress = $event->ipAddress;
        $userAgent = $event->userAgent;

        if ($this->banCheckService->isEmailBanned($email)) {
            $ban = $this->banCheckService->getBanDetails($email);
            
            // Dispatch event
            event(new BannedUserAttempted(
                'password_reset',
                $email,
                $ipAddress,
                $userAgent,
                $ban
            ));

            // Don't throw exception to prevent email enumeration
            // The password reset will appear to succeed but no email will be sent
        }
    }
}
