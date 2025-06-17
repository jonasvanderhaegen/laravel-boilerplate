<?php

declare(strict_types=1);

namespace Modules\Flowbite\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Concerns\PrimaryServiceProviderFunctions;

final class PrimaryServiceProvider extends ServiceProvider
{
    use PrimaryServiceProviderFunctions;

    protected string $name = 'Flowbite';

    protected string $nameLower = 'flowbite';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
