<?php

declare(strict_types=1);

namespace Modules\BanUser\Actions;

use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Events\BannedUserAttempted;
use Illuminate\Validation\ValidationException;

/**
 * Check if credentials are banned before authentication actions.
 */
final class CheckBanBeforeAuthAction
{
    public function __construct(
        private readonly BanCheckService $banCheckService
    ) {}

    /**
     * Check if email or IP is banned.
     *
     * @throws ValidationException
     */
    public function checkLogin(string $email, ?string $ipAddress = null): void
    {
        $ipAddress = $ipAddress ?? request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        if ($this->banCheckService->areCredentialsBanned($email, $ipAddress)) {
            $ban = $this->banCheckService->getBanDetails($email);
            
            // Dispatch event
            event(new BannedUserAttempted(
                'login',
                $email,
                $ipAddress,
                $userAgent,
                $ban
            ));

            throw ValidationException::withMessages([
                'email' => 'Your account has been suspended. Please contact support for more information.',
            ]);
        }
    }

    /**
     * Check if email or IP is banned before registration.
     *
     * @throws ValidationException
     */
    public function checkRegistration(string $email, ?string $ipAddress = null): void
    {
        $ipAddress = $ipAddress ?? request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

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

    /**
     * Check if email is banned before password reset.
     *
     * @throws ValidationException
     */
    public function checkPasswordReset(string $email): void
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

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

            // Don't reveal that the account is banned - just act like we sent the email
            // This prevents email enumeration
            return;
        }
    }
}
