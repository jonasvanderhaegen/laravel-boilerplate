<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Modules\ClassicAuth\Events\EmailVerification\EmailVerificationCompleted;
use Modules\ClassicAuth\Events\Login\LoginFailed;
use Modules\ClassicAuth\Events\Login\LoginSucceeded;
use Modules\ClassicAuth\Events\Login\UserLoggedOut;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetCompleted;
use Modules\ClassicAuth\Events\PasswordReset\PasswordResetRequested;
use Modules\ClassicAuth\Events\Registration\RegistrationFailed;
use Modules\ClassicAuth\Events\Registration\UserRegistered;

/**
 * Log all authentication events for audit purposes.
 */
final class AuditAuthenticationEvents
{
    /**
     * Handle login success.
     */
    public function handleLoginSuccess(LoginSucceeded $event): void
    {
        $this->getLogChannel()->info('User logged in', [
            'event' => 'login_success',
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'remember' => $event->remember,
        ]);
    }

    /**
     * Handle login failure.
     */
    public function handleLoginFailure(LoginFailed $event): void
    {
        $this->getLogChannel()->warning('Login attempt failed', [
            'event' => 'login_failed',
            'email' => $event->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'reason' => $event->failureReason,
        ]);
    }

    /**
     * Handle logout.
     */
    public function handleLogout(UserLoggedOut $event): void
    {
        $this->getLogChannel()->info('User logged out', [
            'event' => 'logout',
            'user_id' => $event->user?->id,
            'email' => $event->user?->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'session_id' => $event->sessionId,
        ]);
    }

    /**
     * Handle registration.
     */
    public function handleRegistration(UserRegistered $event): void
    {
        $this->getLogChannel()->info('New user registered', [
            'event' => 'registration_success',
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'name' => $event->user->name,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'auto_logged_in' => $event->autoLoggedIn,
        ]);
    }

    /**
     * Handle registration failure.
     */
    public function handleRegistrationFailure(RegistrationFailed $event): void
    {
        $this->getLogChannel()->warning('Registration attempt failed', [
            'event' => 'registration_failed',
            'email' => $event->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'reason' => $event->failureReason,
        ]);
    }

    /**
     * Handle password reset request.
     */
    public function handlePasswordResetRequest(PasswordResetRequested $event): void
    {
        $this->getLogChannel()->info('Password reset requested', [
            'event' => 'password_reset_requested',
            'email' => $event->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
        ]);
    }

    /**
     * Handle password reset completion.
     */
    public function handlePasswordResetComplete(PasswordResetCompleted $event): void
    {
        $this->getLogChannel()->info('Password reset completed', [
            'event' => 'password_reset_completed',
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'auto_logged_in' => $event->autoLoggedIn,
        ]);
    }

    /**
     * Handle email verification.
     */
    public function handleEmailVerification(EmailVerificationCompleted $event): void
    {
        $this->getLogChannel()->info('Email verified', [
            'event' => 'email_verified',
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'ip' => $event->ipAddress,
            'user_agent' => $event->userAgent,
        ]);
    }

    /**
     * Subscribe to multiple events.
     *
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            LoginSucceeded::class => 'handleLoginSuccess',
            LoginFailed::class => 'handleLoginFailure',
            UserLoggedOut::class => 'handleLogout',
            UserRegistered::class => 'handleRegistration',
            RegistrationFailed::class => 'handleRegistrationFailure',
            PasswordResetRequested::class => 'handlePasswordResetRequest',
            PasswordResetCompleted::class => 'handlePasswordResetComplete',
            EmailVerificationCompleted::class => 'handleEmailVerification',
        ];
    }

    /**
     * Get the log channel.
     */
    private function getLogChannel(): \Psr\Log\LoggerInterface
    {
        return Log::channel('auth');
    }
}
