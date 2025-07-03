<?php

declare(strict_types=1);

namespace Modules\BanUser\Actions\ClassicAuth;

use Modules\ClassicAuth\Actions\RegisterUserAction as BaseRegisterUserAction;
use Modules\ClassicAuth\DataTransferObjects\RegisterCredentials;
use Modules\ClassicAuth\DataTransferObjects\RegisterResult;
use Modules\BanUser\Actions\CheckBanBeforeAuthAction;
use Illuminate\Support\Timebox;

/**
 * Extended RegisterUserAction that checks for bans before registration.
 */
final class RegisterUserAction extends BaseRegisterUserAction
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
     * Execute the registration action with ban check.
     */
    public function execute(RegisterCredentials $credentials): RegisterResult
    {
        // Check for ban before proceeding
        $this->banChecker->checkRegistration($credentials->email);
        
        // Proceed with original registration logic
        return parent::execute($credentials);
    }
}
