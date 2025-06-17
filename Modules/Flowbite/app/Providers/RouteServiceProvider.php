<?php

declare(strict_types=1);

namespace Modules\Flowbite\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

final class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Flowbite';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapWebRoutes();
        $this->mapAuthRoutes();
    }

    protected function mapAuthRoutes(): void
    {
        Route::middleware('web')->prefix('flowbite')->name('flowbite.')->group(module_path($this->name, '/routes/auth.php'));
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->prefix('flowbite')->name('flowbite.')->group(module_path($this->name, '/routes/web.php'));
    }
}
