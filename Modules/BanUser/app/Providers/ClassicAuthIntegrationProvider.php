<?php

declare(strict_types=1);

namespace Modules\BanUser\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ClassicAuth\Actions\LoginUserAction;
use Modules\ClassicAuth\Actions\RegisterUserAction;
use Modules\ClassicAuth\Actions\RequestPasswordResetAction;
use Modules\BanUser\Actions\ClassicAuth\LoginUserAction as BanAwareLoginUserAction;
use Modules\BanUser\Actions\ClassicAuth\RegisterUserAction as BanAwareRegisterUserAction;
use Modules\BanUser\Actions\ClassicAuth\RequestPasswordResetAction as BanAwareRequestPasswordResetAction;

/**
 * Service provider for integrating ban checks with ClassicAuth module.
 */
final class ClassicAuthIntegrationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Check if ClassicAuth module is active
        if (!class_exists(LoginUserAction::class)) {
            return;
        }

        // Bind our extended actions to replace the original ones
        $this->app->bind(LoginUserAction::class, BanAwareLoginUserAction::class);
        $this->app->bind(RegisterUserAction::class, BanAwareRegisterUserAction::class);
        $this->app->bind(RequestPasswordResetAction::class, BanAwareRequestPasswordResetAction::class);
    }
}
