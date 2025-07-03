<?php

declare(strict_types=1);

namespace Modules\BanUser\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Concerns\PrimaryServiceProviderFunctions;
use Modules\BanUser\Console\Commands\ProcessExpiredBans;
use Modules\BanUser\Services\BanCheckService;
use Modules\BanUser\Actions\CheckBanBeforeAuthAction;
use Modules\BanUser\Http\Middleware\CheckBanned;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;

final class PrimaryServiceProvider extends ServiceProvider
{
    use PrimaryServiceProviderFunctions;

    protected string $name = 'BanUser';

    protected string $nameLower = 'banuser';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        $this->registerMiddleware();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ClassicAuthIntegrationProvider::class);
        
        // Register services
        $this->app->singleton(BanCheckService::class);
        $this->app->singleton(CheckBanBeforeAuthAction::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            ProcessExpiredBans::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            // Run every hour to process expired bans
            $schedule->command('bans:process-expired')->hourly();
        });
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        
        // Register the middleware alias
        $router->aliasMiddleware('check.banned', CheckBanned::class);
        
        // Add to web middleware group if you want it applied globally
        // $router->pushMiddlewareToGroup('web', CheckBanned::class);
    }
}
