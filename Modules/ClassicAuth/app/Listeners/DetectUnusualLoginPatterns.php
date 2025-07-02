<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Exception;
use Modules\ClassicAuth\Events\Login\LoginSucceeded;
use Modules\ClassicAuth\Services\LoginPatternDetector;

/**
 * Detect unusual login patterns for security notifications.
 */
final readonly class DetectUnusualLoginPatterns
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        private LoginPatternDetector $detector
    ) {}

    /**
     * Handle the event.
     */
    public function handle(LoginSucceeded $event): void
    {
        // Don't analyze for API or automated logins
        if (request()->is('api/*')) {
            return;
        }

        try {
            $this->detector->analyzeLogin(
                $event->user,
                $event->ipAddress,
                $event->userAgent
            );
        } catch (Exception $e) {
            // Log error but don't fail the login
            logger()->error('Failed to analyze login pattern', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
