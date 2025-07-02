<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\ClassicAuth\Events\Login\LoginSucceeded;
use Modules\ClassicAuth\Events\Login\LoginFailed;
use Modules\ClassicAuth\Events\Registration\UserRegistered;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetCompleted;

/**
 * Track authentication metrics for monitoring and analytics.
 */
class TrackAuthenticationMetrics
{
    /**
     * Handle login success events.
     */
    public function handleLoginSuccess(LoginSucceeded $event): void
    {
        // Increment daily login counter
        $this->incrementDailyCounter('logins:successful');
        
        // Track unique users
        $this->trackUniqueUser($event->user->id);
        
        // Track by hour for analytics
        $this->incrementHourlyCounter('logins:successful');
    }

    /**
     * Handle login failure events.
     */
    public function handleLoginFailure(LoginFailed $event): void
    {
        // Increment failure counter
        $this->incrementDailyCounter('logins:failed');
        
        // Track failure reasons
        $this->incrementDailyCounter("logins:failed:{$event->failureReason}");
        
        // Track by hour for pattern detection
        $this->incrementHourlyCounter('logins:failed');
    }

    /**
     * Handle registration events.
     */
    public function handleRegistration(UserRegistered $event): void
    {
        // Increment registration counter
        $this->incrementDailyCounter('registrations');
        
        // Track registration source (if available)
        if ($event->autoLoggedIn) {
            $this->incrementDailyCounter('registrations:auto_login');
        }
    }

    /**
     * Handle password reset events.
     */
    public function handlePasswordReset(PasswordResetCompleted $event): void
    {
        // Increment password reset counter
        $this->incrementDailyCounter('password_resets');
        
        // Track auto-login after reset
        if ($event->autoLoggedIn) {
            $this->incrementDailyCounter('password_resets:auto_login');
        }
    }

    /**
     * Subscribe to multiple events.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array<string, string>
     */
    public function subscribe($events): array
    {
        return [
            LoginSucceeded::class => 'handleLoginSuccess',
            LoginFailed::class => 'handleLoginFailure',
            UserRegistered::class => 'handleRegistration',
            PasswordResetCompleted::class => 'handlePasswordReset',
        ];
    }

    /**
     * Increment a daily counter.
     */
    private function incrementDailyCounter(string $key): void
    {
        $cacheKey = "auth:metrics:daily:{$key}:" . now()->format('Y-m-d');
        Cache::increment($cacheKey);
        
        // Expire after 7 days
        Cache::put($cacheKey, Cache::get($cacheKey, 0), now()->addDays(7));
    }

    /**
     * Increment an hourly counter.
     */
    private function incrementHourlyCounter(string $key): void
    {
        $cacheKey = "auth:metrics:hourly:{$key}:" . now()->format('Y-m-d:H');
        Cache::increment($cacheKey);
        
        // Expire after 48 hours
        Cache::put($cacheKey, Cache::get($cacheKey, 0), now()->addHours(48));
    }

    /**
     * Track unique user.
     */
    private function trackUniqueUser(int $userId): void
    {
        $cacheKey = "auth:metrics:unique_users:" . now()->format('Y-m-d');
        $users = Cache::get($cacheKey, []);
        
        if (!in_array($userId, $users)) {
            $users[] = $userId;
            Cache::put($cacheKey, $users, now()->addDays(7));
        }
    }
}
