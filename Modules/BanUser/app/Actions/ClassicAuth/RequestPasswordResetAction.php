<?php

declare(strict_types=1);

namespace Modules\BanUser\Actions\ClassicAuth;

use Modules\ClassicAuth\Actions\RequestPasswordResetAction as BaseRequestPasswordResetAction;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetRequestCredentials;
use Modules\ClassicAuth\DataTransferObjects\PasswordResetRequestResult;
use Modules\BanUser\Actions\CheckBanBeforeAuthAction;
use Illuminate\Support\Timebox;

/**
 * Extended RequestPasswordResetAction that checks for bans before password reset.
 */
final class RequestPasswordResetAction extends BaseRequestPasswordResetAction
{
    private CheckBanBeforeAuthAction $banChecker;

    public function __construct(
        Timebox $timebox,
        CheckBanBeforeAuthAction $banChecker
    ) {
        parent::__construct($timebox);
        $this->banChecker = $banChecker;
    }

    /**
     * Execute the password reset request action with ban check.
     */
    public function execute(PasswordResetRequestCredentials $credentials): PasswordResetRequestResult
    {
        // Check for ban (doesn't throw exception for password reset)
        $this->banChecker->checkPasswordReset($credentials->email);
        
        // Proceed with original password reset logic
        return parent::execute($credentials);
    }
}
