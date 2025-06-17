<?php

declare(strict_types=1);

namespace Modules\Core\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Core\Http\Middleware\DetectDevice;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

final class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'Core';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern-based filters.
     */
    public function boot(): void
    {
        parent::boot();

        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('doNotCacheResponse', DoNotCacheResponse::class);
        $router->pushMiddlewareToGroup('web', DetectDevice::class);
        $router->pushMiddlewareToGroup('web', CacheResponse::class);

        // 60 requests / minute for guest static pages
        RateLimiter::for('info-pages',
            fn (Request $r) => Limit::perMinute(60)->by($r->ip()));

        // 10 requests / minute for guest auth pages (login/register/reset)
        RateLimiter::for('guest-auth',
            fn (Request $r) => Limit::perMinute(10)->by($r->ip()));

        // 30 requests / minute across user settings & messages
        RateLimiter::for('user-actions', function (Request $request) {
            $user = $request->user();

            return Limit::perMinute(30)->by(
                $user ? $user->id : $request->ip()
            );
        });

        // 5 posts / minute for public form
        RateLimiter::for('public-form',
            fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void {}
}
