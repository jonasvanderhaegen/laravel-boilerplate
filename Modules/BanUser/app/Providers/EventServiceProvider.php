<?php

declare(strict_types=1);

namespace Modules\BanUser\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\BanUser\Events\UserBanned;
use Modules\BanUser\Events\BannedUserAttempted;
use Modules\BanUser\Listeners\LogBanActivity;
use Modules\BanUser\Listeners\NotifyBannedUserAttempt;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        UserBanned::class => [
            LogBanActivity::class,
        ],
        BannedUserAttempted::class => [
            NotifyBannedUserAttempt::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
