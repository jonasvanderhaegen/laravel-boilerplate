<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Actions;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Modules\ClassicAuth\DataTransferObjects\LoginCredentials;
use Modules\ClassicAuth\DataTransferObjects\LoginResult;
use Modules\ClassicAuth\Models\LoginAttempt;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;

/**
 * Handle user login authentication action.
 *
 * This action encapsulates:
 * - Authentication logic
 * - Rate limiting (IP and email-based)
 * - Session management
 * - Event dispatching
 * - Login attempt logging
 * - Timing attack prevention using Laravel's Timebox
 */
final class LoginUserAction
{
    use RateLimitDurations, WithRateLimiting;

    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    private const MAX_EMAIL_ATTEMPTS = 15;

    private const EMAIL_DECAY_SECONDS = 3600;

    public function __construct(
        private readonly Timebox $timebox
    ) {}

    /**
     * Execute the login action.
     *
     *
     * @throws ValidationException
     * @throws TooManyRequestsException
     */
    public function execute(LoginCredentials $credentials): LoginResult
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        // Check IP-based rate limiting first (outside timebox for fast fail)
        $this->checkIpRateLimit($credentials, $ipAddress, $userAgent);

        // Use timebox to prevent timing attacks
        // Convert milliseconds to microseconds (300ms = 300,000 microseconds)
        $minimumMicroseconds = config('classicauth.security.auth_min_time_ms', 300) * 1000;

        return $this->timebox->call(function (Timebox $timebox) use ($credentials, $ipAddress, $userAgent) {
            // Attempt authentication
            $authenticated = Auth::attempt($credentials->toAuthArray(), $credentials->remember);

            if (! $authenticated) {
                // Check email-based rate limiting for failed attempts
                $this->checkEmailRateLimit($credentials, $ipAddress, $userAgent);

                // Log failed attempt
                if (config('classicauth.tracking.enabled', true)) {
                    LoginAttempt::logFailure(
                        $credentials->email,
                        $ipAddress,
                        $userAgent,
                        LoginAttempt::FAILURE_INVALID_CREDENTIALS
                    );
                }

                // Don't call returnEarly() for failures - maintain full timing
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // Success - allow early return for better UX
            $timebox->returnEarly();

            // Handle successful login (no session regeneration here)
            return $this->handleSuccessfulLogin($credentials->remember, $ipAddress, $userAgent);
        }, $minimumMicroseconds);
    }

    /**
     * Get IP rate limit settings from config.
     */
    private function getIpRateLimitSettings(): array
    {
        return [
            'max_attempts' => config('classicauth.rate_limiting.ip.max_attempts', self::MAX_ATTEMPTS),
            'decay_seconds' => config('classicauth.rate_limiting.ip.decay_seconds', self::DECAY_SECONDS),
        ];
    }

    /**
     * Get email rate limit settings from config.
     */
    private function getEmailRateLimitSettings(): array
    {
        return [
            'max_attempts' => config('classicauth.rate_limiting.email.max_attempts', self::MAX_EMAIL_ATTEMPTS),
            'decay_seconds' => config('classicauth.rate_limiting.email.decay_seconds', self::EMAIL_DECAY_SECONDS),
        ];
    }

    /**
     * Get default redirect route from config.
     */
    private function getDefaultRedirect(): string
    {
        $redirect = config('classicauth.defaults.login_redirect', 'dashboard');

        // If it's a route name, convert to URL
        if (Route::has($redirect)) {
            return route($redirect);
        }

        // Otherwise return as-is (could be a path)
        return $redirect;
    }

    /**
     * Check IP-based rate limiting.
     *
     * @throws TooManyRequestsException
     */
    private function checkIpRateLimit(LoginCredentials $credentials, string $ipAddress, string $userAgent): void
    {
        $ipSettings = $this->getIpRateLimitSettings();

        try {
            $this->rateLimit($ipSettings['max_attempts'], $ipSettings['decay_seconds']);
        } catch (TooManyRequestsException $e) {
            // Log rate-limited attempt
            if (config('classicauth.tracking.enabled', true)) {
                LoginAttempt::logFailure(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    LoginAttempt::FAILURE_RATE_LIMITED
                );
            }
            throw $e;
        }
    }

    /**
     * Check email-based rate limiting for failed attempts.
     *
     * @throws TooManyRequestsException
     */
    private function checkEmailRateLimit(LoginCredentials $credentials, string $ipAddress, string $userAgent): void
    {
        $emailSettings = $this->getEmailRateLimitSettings();

        try {
            $this->rateLimitByEmail(
                $emailSettings['max_attempts'],
                $this->longDuration(90, $emailSettings['decay_seconds']),
                $credentials->email,
                'login'
            );
        } catch (TooManyRequestsException $e) {
            // Log rate-limited attempt
            if (config('classicauth.tracking.enabled', true)) {
                LoginAttempt::logFailure(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    LoginAttempt::FAILURE_RATE_LIMITED
                );
            }
            throw $e;
        }
    }

    /**
     * Handle successful login.
     */
    private function handleSuccessfulLogin(bool $remember, string $ipAddress, string $userAgent): LoginResult
    {
        $user = Auth::user();
        $intendedUrl = session()->pull('url.intended', $this->getDefaultRedirect());

        DB::transaction(function () use ($user, $ipAddress, $userAgent) {
            // Clear rate limiters
            $this->clearRateLimiter();
            $this->clearRateLimiter('attemptLogin');

            // Clear email-based rate limiter
            $this->clearEmailRateLimiter($user->email, 'login');

            // Update last login timestamp if columns exist
            $updateData = [];
            if (Schema::hasColumn('users', 'last_login_at')) {
                $updateData['last_login_at'] = now();
            }
            if (Schema::hasColumn('users', 'last_login_ip')) {
                $updateData['last_login_ip'] = $ipAddress;
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            // Log successful attempt
            if (config('classicauth.tracking.enabled', true)) {
                LoginAttempt::logSuccess($user, $ipAddress, $userAgent);
            }

            // Clear any lingering authentication data
            session()->forget(['login.email', 'login.attempts']);
        });

        // Create login result
        $loginResult = LoginResult::success($user, $intendedUrl, $remember);

        // Store session data if enabled
        if (config('classicauth.session.store_login_data', true)) {
            session()->put($loginResult->getSessionData());
        }

        // Dispatch login event
        event(new Login(Auth::guard(), $user, $remember));
        event(new Authenticated(Auth::guard(), $user));

        // Note: Session regeneration is handled by the calling component
        // to avoid Livewire CSRF token issues

        return $loginResult;
    }
}
