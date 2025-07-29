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

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\ClassicAuth\Console\Commands\CleanupLoginAttempts::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     */
    // @codeCoverageIgnoreStart
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        // Bind LoginUserAction with Timebox dependency
        $this->app->bind(fn ($app): \Modules\ClassicAuth\Actions\LoginUserAction => new \Modules\ClassicAuth\Actions\LoginUserAction(
            $app->make(\Illuminate\Support\Timebox::class)
        ));
    }
    // @codeCoverageIgnoreEnd
}
