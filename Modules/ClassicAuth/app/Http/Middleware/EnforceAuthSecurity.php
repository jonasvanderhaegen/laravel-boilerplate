<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce security policies based on authentication events.
 */
final class EnforceAuthSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Check if user has suspicious activity flags
        if ($this->hasSuspiciousActivity($user)) {
            return $this->handleSuspiciousUser($request, $next);
        }

        // Check if user logged in from new device recently
        if ($this->hasRecentNewDevice($user)) {
            return $this->handleNewDeviceVerification($request, $next);
        }

        // Check if account needs additional verification
        if ($this->needsAdditionalVerification($user)) {
            return $this->requireAdditionalVerification($request);
        }

        return $next($request);
    }

    /**
     * Check if user has suspicious activity flags.
     */
    private function hasSuspiciousActivity($user): bool
    {
        $cacheKey = "user:{$user->id}:suspicious_activity";

        return Cache::has($cacheKey);
    }

    /**
     * Handle suspicious user.
     */
    private function handleSuspiciousUser(Request $request, Closure $next): Response
    {
        // Log the suspicious access
        logger()->warning('Suspicious user accessing protected resource', [
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'path' => $request->path(),
        ]);

        // You could:
        // - Require 2FA
        // - Limit access to sensitive features
        // - Send notification to admin
        // - Log out the user

        // For now, just add a warning header
        $response = $next($request);
        $response->headers->set('X-Auth-Warning', 'suspicious-activity');

        return $response;
    }

    /**
     * Check if user has recent new device login.
     */
    private function hasRecentNewDevice($user): bool
    {
        $cacheKey = "user:{$user->id}:new_device_login";
        $lastNewDevice = Cache::get($cacheKey);

        // If new device login in last hour
        return $lastNewDevice && $lastNewDevice > now()->subHour()->timestamp;
    }

    /**
     * Handle new device verification.
     */
    private function handleNewDeviceVerification(Request $request, Closure $next): Response
    {
        // Skip verification for non-sensitive routes
        if ($this->isNonSensitiveRoute($request)) {
            return $next($request);
        }

        // Check if already verified this session
        if (session('device_verified', false)) {
            return $next($request);
        }

        // Redirect to device verification
        return redirect()->route('auth.verify-device')
            ->with('warning', 'Please verify your new device before continuing.');
    }

    /**
     * Check if user needs additional verification.
     */
    private function needsAdditionalVerification($user): bool
    {
        // Check various conditions
        $conditions = [
            'requires_2fa' => Cache::get("user:{$user->id}:requires_2fa", false),
            'password_expired' => $this->isPasswordExpired($user),
            'account_flagged' => Cache::get("user:{$user->id}:account_flagged", false),
        ];

        return array_filter($conditions);
    }

    /**
     * Require additional verification.
     */
    private function requireAdditionalVerification(Request $request): Response
    {
        return redirect()->route('auth.additional-verification')
            ->with('info', 'Additional verification required for your security.');
    }

    /**
     * Check if password is expired.
     */
    private function isPasswordExpired($user): bool
    {
        // If user has password_changed_at column
        if (! isset($user->password_changed_at)) {
            return false;
        }

        $passwordAge = now()->diffInDays($user->password_changed_at);
        $maxAge = config('classicauth.security.password_expiry_days', 90);

        return $passwordAge > $maxAge;
    }

    /**
     * Check if route is non-sensitive.
     */
    private function isNonSensitiveRoute(Request $request): bool
    {
        $nonSensitiveRoutes = [
            'logout',
            'home',
            'profile.show',
            'auth.verify-device',
        ];

        return in_array($request->route()?->getName(), $nonSensitiveRoutes);
    }
}
