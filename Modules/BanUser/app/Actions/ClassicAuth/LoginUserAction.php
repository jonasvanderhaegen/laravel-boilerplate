<?php

declare(strict_types=1);

namespace Modules\BanUser\Actions\ClassicAuth;

use Modules\ClassicAuth\Actions\LoginUserAction as BaseLoginUserAction;
use Modules\ClassicAuth\DataTransferObjects\LoginCredentials;
use Modules\ClassicAuth\DataTransferObjects\LoginResult;
use Modules\BanUser\Actions\CheckBanBeforeAuthAction;
use Illuminate\Support\Timebox;

/**
 * Extended LoginUserAction that checks for bans before login.
 */
final class LoginUserAction extends BaseLoginUserAction
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
     * Execute the login action with ban check.
     */
    public function execute(LoginCredentials $credentials): LoginResult
    {
        // Check for ban before proceeding
        $this->banChecker->checkLogin($credentials->email);
        
        // Proceed with original login logic
        return parent::execute($credentials);
    }
}
