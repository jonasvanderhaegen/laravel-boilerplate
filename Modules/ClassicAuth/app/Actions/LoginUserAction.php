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
use Modules\ClassicAuth\Events\Login\LoginFailed;
use Modules\ClassicAuth\Events\Login\LoginSucceeded;
use Modules\ClassicAuth\Events\Security\SuspiciousActivityDetected;
use Modules\ClassicAuth\Events\Security\TooManyFailedAttempts;
use Modules\ClassicAuth\Models\LoginAttempt;
use Modules\Core\Concerns\RateLimitDurations;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;
use Throwable;

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
     * @throws TooManyRequestsException|Throwable
     */
    public function execute(LoginCredentials $credentials): LoginResult
    {
        $ipAddress = request()->ip() ?? 'unknown';
        $userAgent = request()->userAgent() ?? 'unknown';

        // Ray: Log login attempt start
        ray()
            ->showApp()
            ->newScreen('Login functionality')
            ->label('[LoginUserAction::execute] Login Attempt Started')
            ->table([
                'Email' => $credentials->email,
                'IP Address' => $ipAddress,
                'User Agent' => $userAgent,
                'Remember Me' => $credentials->remember ? 'Yes' : 'No',
                'Timestamp' => now()->toDateTimeString(),
            ])
            ->color('blue');

        // Check IP-based rate limiting first (outside timebox for fast fail)
        $this->checkIpRateLimit($credentials, $ipAddress, $userAgent);

        // Use timebox to prevent timing attacks
        // Convert milliseconds to microseconds (300ms = 300,000 microseconds)
        $minimumMicroseconds = config('classicauth.security.auth_min_time_ms', 300) * 1000;

        return $this->timebox->call(function (Timebox $timebox) use ($credentials, $ipAddress, $userAgent) {
            // Ray: Log authentication attempt
            ray()
                ->label('[LoginUserAction::execute -> timebox] Authenticating User')
                ->table([
                    'Email' => $credentials->email,
                    'Remember' => $credentials->remember ? 'Yes' : 'No',
                ])
                ->color('purple');

            // Attempt authentication
            $authenticated = Auth::attempt($credentials->toAuthArray(), $credentials->remember);

            if (! $authenticated) {
                // Ray: Log authentication failure
                ray()
                    ->label('[LoginUserAction::execute -> if (!authenticated)] ❌ Authentication Failed')
                    ->table([
                        'Email' => $credentials->email,
                        'IP' => $ipAddress,
                        'Reason' => 'Invalid credentials',
                    ])
                    ->color('red');

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

                // Dispatch failed login event
                event(new LoginFailed(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    'invalid_credentials'
                ));

                // Don't call returnEarly() for failures - maintain full timing
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            // Ray: Log authentication success
            ray()
                ->label('[LoginUserAction::execute -> after Auth::attempt success] ✅ Authentication Successful')
                ->table([
                    'Email' => $credentials->email,
                    'IP' => $ipAddress,
                ])
                ->color('green')
                ->notify('User logged in: '.$credentials->email);

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

        // Ray: Log IP rate limit check
        ray()
            ->label('[LoginUserAction::checkIpRateLimit] IP Rate Limit Check')
            ->table([
                'IP' => $ipAddress,
                'Max Attempts' => $ipSettings['max_attempts'],
                'Decay Seconds' => $ipSettings['decay_seconds'],
            ])
            ->color('gray');

        try {
            $this->rateLimit($ipSettings['max_attempts'], $ipSettings['decay_seconds']);
        } catch (TooManyRequestsException $e) {
            // Ray: Log IP rate limit exceeded
            ray()
                ->label('[LoginUserAction::checkIpRateLimit -> catch] ⚠️ IP Rate Limit Exceeded')
                ->table([
                    'IP' => $ipAddress,
                    'Email' => $credentials->email,
                    'Message' => $e->getMessage(),
                ])
                ->color('red')
                ->notify('IP Rate Limit Exceeded for '.$ipAddress);

            // Log rate-limited attempt
            if (config('classicauth.tracking.enabled', true)) {
                LoginAttempt::logFailure(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    LoginAttempt::FAILURE_RATE_LIMITED
                );
            }

            // Dispatch security event
            event(new TooManyFailedAttempts(
                'login',
                $ipAddress,
                $ipSettings['max_attempts'],
                $ipSettings['decay_seconds'],
                $ipAddress
            ));

            // Dispatch failed login event
            event(new LoginFailed(
                $credentials->email,
                $ipAddress,
                $userAgent,
                'rate_limited'
            ));

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

        // Ray: Log email rate limit check
        ray()
            ->label('[LoginUserAction::checkEmailRateLimit] Email Rate Limit Check')
            ->table([
                'Email' => $credentials->email,
                'Max Attempts' => $emailSettings['max_attempts'],
                'Decay Seconds' => $emailSettings['decay_seconds'],
            ])
            ->color('gray');

        try {
            $this->rateLimitByEmail(
                $emailSettings['max_attempts'],
                $this->longDuration(90, $emailSettings['decay_seconds']),
                $credentials->email,
                'login'
            );
        } catch (TooManyRequestsException $e) {
            // Ray: Log email rate limit exceeded
            ray()
                ->label('[LoginUserAction::checkEmailRateLimit -> catch] ⚠️ Email Rate Limit Exceeded')
                ->table([
                    'Email' => $credentials->email,
                    'IP' => $ipAddress,
                    'Message' => $e->getMessage(),
                ])
                ->color('red')
                ->notify('Email Rate Limit Exceeded for '.$credentials->email);

            // Log rate-limited attempt
            if (config('classicauth.tracking.enabled', true)) {
                LoginAttempt::logFailure(
                    $credentials->email,
                    $ipAddress,
                    $userAgent,
                    LoginAttempt::FAILURE_RATE_LIMITED
                );
            }

            // Check for suspicious activity
            $recentFailures = LoginAttempt::where('email', $credentials->email)
                ->where('successful', false)
                ->where('created_at', '>=', now()->subHours(1))
                ->count();

            if ($recentFailures > 10) {
                // Ray: Log suspicious brute force activity
                ray()
                    ->label('[LoginUserAction::checkEmailRateLimit -> if (recentFailures > 10)] 🚨 Suspicious Activity: Brute Force')
                    ->table([
                        'Email' => $credentials->email,
                        'IP' => $ipAddress,
                        'Recent Failures' => $recentFailures,
                        'Type' => 'brute_force',
                    ])
                    ->color('red')
                    ->notify('Brute force detected for '.$credentials->email);

                event(new SuspiciousActivityDetected(
                    'brute_force',
                    $ipAddress,
                    $credentials->email,
                    ['recent_failures' => $recentFailures]
                ));
            }

            // Check for multiple IPs
            $recentIPs = LoginAttempt::where('email', $credentials->email)
                ->where('created_at', '>=', now()->subHours(6))
                ->distinct('ip_address')
                ->count('ip_address');

            if ($recentIPs > 5) {
                // Ray: Log suspicious multiple IPs activity
                ray()
                    ->label('[LoginUserAction::checkEmailRateLimit -> if (recentIPs > 5)] 🚨 Suspicious Activity: Multiple IPs')
                    ->table([
                        'Email' => $credentials->email,
                        'Current IP' => $ipAddress,
                        'Unique IP Count' => $recentIPs,
                        'Type' => 'multiple_ips',
                    ])
                    ->color('red')
                    ->notify('Multiple IPs detected for '.$credentials->email);

                event(new SuspiciousActivityDetected(
                    'multiple_ips',
                    $ipAddress,
                    $credentials->email,
                    ['ip_count' => $recentIPs]
                ));
            }

            // Dispatch security event
            event(new TooManyFailedAttempts(
                'login',
                $credentials->email,
                $emailSettings['max_attempts'],
                $emailSettings['decay_seconds'],
                $ipAddress
            ));

            throw $e;
        }
    }

    /**
     * Handle successful login.
     *
     * @throws Throwable
     */
    private function handleSuccessfulLogin(bool $remember, string $ipAddress, string $userAgent): LoginResult
    {
        $user = Auth::user();
        $intendedUrl = session()->pull('url.intended', $this->getDefaultRedirect());

        // Ray: Log successful login details
        ray()
            ->label('[LoginUserAction::handleSuccessfulLogin] 🎉 Login Success Handler')
            ->table([
                'User ID' => $user->id,
                'User Email' => $user->email,
                'User Name' => $user->name ?? 'N/A',
                'Intended URL' => $intendedUrl,
                'IP' => $ipAddress,
            ])
            ->color('green');

        DB::transaction(function () use ($user, $ipAddress, $userAgent) {
            // Clear rate limiters
            $this->clearRateLimiter('checkIpRateLimit', self::class);

            // Clear the rate limiter that the Livewire component checks
            $this->clearRateLimiter('execute', self::class);

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

        // Ray: Log login result
        ray()
            ->label('[LoginUserAction::handleSuccessfulLogin -> after LoginResult::success] Login Result Created')
            ->table([
                'User ID' => $loginResult->user->id,
                'Intended URL' => $loginResult->intendedUrl,
                'Was Remembered' => $loginResult->wasRemembered ? 'Yes' : 'No',
            ])
            ->color('green');

        // Store session data if enabled
        if (config('classicauth.session.store_login_data', true)) {
            session()->put($loginResult->getSessionData());
        }

        event(new Login(Auth::guard(), $user, $remember));
        event(new Authenticated(Auth::guard(), $user));
        // Dispatch our custom success event
        event(new LoginSucceeded($user, $ipAddress, $userAgent, $remember));

        // Note: Session regeneration is handled by the calling component
        // to avoid Livewire CSRF token issues

        return $loginResult;
    }
}
