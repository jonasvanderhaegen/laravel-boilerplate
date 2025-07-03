<?php

declare(strict_types=1);

namespace Modules\BanUser\Listeners;

use Modules\ClassicAuth\Events\LoginAttempted;
use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Events\BannedUserAttempted;
use Illuminate\Validation\ValidationException;

/**
 * Check for bans when login is attempted.
 */
final class CheckBanOnLogin
{
    public function __construct(
        private readonly BanCheckService $banCheckService
    ) {}

    /**
     * Handle the event.
     *
     * @throws ValidationException
     */
    public function handle(LoginAttempted $event): void
    {
        $email = $event->credentials['email'] ?? null;
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        if (!$email) {
            return;
        }

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
}
