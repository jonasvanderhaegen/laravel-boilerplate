<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Concerns\PrimaryServiceProviderFunctions;

final class PrimaryServiceProvider extends ServiceProvider
{
    use PrimaryServiceProviderFunctions;

    protected string $name = 'ClassicAuth';

    protected string $nameLower = 'classicauth';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        // Merge logging configuration
        $this->mergeLoggingConfig();

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\ClassicAuth\Console\Commands\CleanupLoginAttempts::class,
                \Modules\ClassicAuth\Console\Commands\AuthMetrics::class,
                \Modules\ClassicAuth\Console\Commands\TestAuthEvents::class,
                \Modules\ClassicAuth\Console\Commands\ClearAuthData::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Bind LoginUserAction with Timebox dependency
        $this->app->bind(fn ($app): \Modules\ClassicAuth\Actions\LoginUserAction => new \Modules\ClassicAuth\Actions\LoginUserAction(
            $app->make(\Illuminate\Support\Timebox::class)
        ));

        // Register other action bindings
        $this->app->bind(fn ($app): \Modules\ClassicAuth\Actions\RegisterUserAction => new \Modules\ClassicAuth\Actions\RegisterUserAction(
            $app->make(\Illuminate\Support\Timebox::class)
        ));

        $this->app->bind(fn ($app): \Modules\ClassicAuth\Actions\RequestPasswordResetAction => new \Modules\ClassicAuth\Actions\RequestPasswordResetAction(
            $app->make(\Illuminate\Support\Timebox::class)
        ));

        $this->app->bind(fn ($app): \Modules\ClassicAuth\Actions\ResetPasswordAction => new \Modules\ClassicAuth\Actions\ResetPasswordAction(
            $app->make(\Illuminate\Support\Timebox::class)
        ));

        $this->app->bind(fn ($app): \Modules\ClassicAuth\Actions\ResendVerificationEmailAction => new \Modules\ClassicAuth\Actions\ResendVerificationEmailAction(
            $app->make(\Illuminate\Support\Timebox::class)
        ));

        // Register services
        $this->app->singleton(\Modules\ClassicAuth\Services\LoginPatternDetector::class);
    }

    /**
     * Merge logging configuration for the auth channel.
     */
    protected function mergeLoggingConfig(): void
    {
        config([
            'logging.channels' => array_merge(
                config('logging.channels', []),
                config('classicauth.logging.channels', [])
            ),
        ]);
    }
}
