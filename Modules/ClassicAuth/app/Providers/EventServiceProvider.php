<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        // Login Events
        \Modules\ClassicAuth\Events\LoginAttempted::class => [
            \Modules\ClassicAuth\Listeners\LogLoginAttempt::class,
        ],
        \Modules\ClassicAuth\Events\Login\LoginSucceeded::class => [
            // Add listeners as needed
            \Modules\ClassicAuth\Listeners\CleanupAuthenticationData::class,
            \Modules\ClassicAuth\Listeners\DetectUnusualLoginPatterns::class,
        ],
        \Modules\ClassicAuth\Events\Login\LoginFailed::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\Login\UserLoggedOut::class => [
            // Add listeners as needed
        ],

        // Registration Events
        \Modules\ClassicAuth\Events\Registration\UserRegistering::class => [
            // Add listeners for pre-registration checks
        ],
        \Modules\ClassicAuth\Events\Registration\UserRegistered::class => [
            // Add listeners for post-registration actions
            \Modules\ClassicAuth\Listeners\SendWelcomeEmail::class,
        ],
        \Modules\ClassicAuth\Events\Registration\RegistrationFailed::class => [
            // Add listeners as needed
        ],

        // Password Reset Events
        \Modules\ClassicAuth\Events\PasswordReset\PasswordResetRequested::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\PasswordReset\PasswordResetLinkSent::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\PasswordReset\PasswordResetCompleted::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\PasswordReset\PasswordResetFailed::class => [
            // Add listeners as needed
        ],

        // Email Verification Events
        \Modules\ClassicAuth\Events\EmailVerification\EmailVerificationRequested::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\EmailVerification\EmailVerificationLinkSent::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\EmailVerification\EmailVerificationCompleted::class => [
            // Add listeners as needed
        ],

        // Security Events
        \Modules\ClassicAuth\Events\Security\SuspiciousActivityDetected::class => [
            \Modules\ClassicAuth\Listeners\Security\NotifySuspiciousActivity::class,
        ],
        \Modules\ClassicAuth\Events\Security\TooManyFailedAttempts::class => [
            // Add listeners for rate limiting responses
        ],

        // User Notification Events
        \Modules\ClassicAuth\Events\UserNotifications\NewDeviceLogin::class => [
            // Add listeners as needed
        ],
        \Modules\ClassicAuth\Events\UserNotifications\UnusualLocationLogin::class => [
            // Add listeners as needed
        ],
    ];

    /**
     * The subscribers to register.
     *
     * @var array<int, string>
     */
    protected $subscribe = [
        \Modules\ClassicAuth\Listeners\TrackAuthenticationMetrics::class,
        \Modules\ClassicAuth\Listeners\AuditAuthenticationEvents::class,
        \Modules\ClassicAuth\Listeners\SendSecurityAlertEmail::class,
        \Modules\ClassicAuth\Listeners\SendAuthWebhooks::class,
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
