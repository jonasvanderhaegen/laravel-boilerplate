<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Modules\ClassicAuth\Events\UserNotifications\NewDeviceLogin;
use Modules\ClassicAuth\Events\UserNotifications\UnusualLocationLogin;
use Modules\ClassicAuth\Models\LoginAttempt;

/**
 * Service to detect unusual login patterns and trigger notifications.
 */
final class LoginPatternDetector
{
    /**
     * Analyze a successful login for unusual patterns.
     */
    public function analyzeLogin(User $user, string $ipAddress, string $userAgent): void
    {
        $this->checkNewDevice($user, $ipAddress, $userAgent);
        $this->checkUnusualLocation($user, $ipAddress, $userAgent);
        $this->updateLoginHistory($user, $ipAddress, $userAgent);
    }

    /**
     * Check if this is a login from a new device.
     */
    private function checkNewDevice(User $user, string $ipAddress, string $userAgent): void
    {
        $deviceFingerprint = $this->getDeviceFingerprint($userAgent);
        $knownDevices = $this->getKnownDevices($user);

        if (! in_array($deviceFingerprint, $knownDevices)) {
            // This is a new device
            event(new NewDeviceLogin(
                $user,
                $ipAddress,
                $userAgent,
                $this->getLocationFromIP($ipAddress),
                $this->parseDeviceInfo($userAgent)
            ));

            // Add to known devices
            $this->addKnownDevice($user, $deviceFingerprint);

            // Set cache flag for middleware
            Cache::put(
                "user:{$user->id}:new_device_login",
                now()->timestamp,
                now()->addHours(24)
            );
        }
    }

    /**
     * Check if this is a login from an unusual location.
     */
    private function checkUnusualLocation(User $user, string $ipAddress, string $userAgent): void
    {
        $currentLocation = $this->getLocationFromIP($ipAddress);
        if (! $currentLocation) {
            return;
        }

        $lastLogin = $this->getLastSuccessfulLogin($user);
        if (! $lastLogin) {
            return;
        }

        $lastLocation = $this->getLocationFromIP($lastLogin->ip_address);
        if (! $lastLocation) {
            return;
        }

        // Calculate distance between locations
        $distance = $this->calculateDistance(
            $currentLocation['lat'] ?? 0,
            $currentLocation['lon'] ?? 0,
            $lastLocation['lat'] ?? 0,
            $lastLocation['lon'] ?? 0
        );

        // Check if it's a significant distance
        if ($distance > 100) { // More than 100km
            event(new UnusualLocationLogin(
                $user,
                $ipAddress,
                $userAgent,
                $currentLocation['city'] ?? 'Unknown',
                $lastLocation['city'] ?? 'Unknown',
                $distance
            ));
        }
    }

    /**
     * Get a device fingerprint from user agent.
     */
    private function getDeviceFingerprint(string $userAgent): string
    {
        // Simple fingerprinting - in production, use a proper library
        return md5($userAgent);
    }

    /**
     * Get known devices for a user.
     *
     * @return array<string>
     */
    private function getKnownDevices(User $user): array
    {
        $cacheKey = "user:{$user->id}:known_devices";

        return Cache::get($cacheKey, []);
    }

    /**
     * Add a device to known devices.
     */
    private function addKnownDevice(User $user, string $fingerprint): void
    {
        $cacheKey = "user:{$user->id}:known_devices";
        $devices = $this->getKnownDevices($user);

        // Keep only last 10 devices
        $devices[] = $fingerprint;
        $devices = array_unique(array_slice($devices, -10));

        Cache::put($cacheKey, $devices, now()->addMonths(6));
    }

    /**
     * Get location from IP address.
     *
     * @return array{city?: string, country?: string, lat?: float, lon?: float}|null
     */
    private function getLocationFromIP(string $ipAddress): ?array
    {
        // TODO: Implement actual IP geolocation
        // For now, return mock data
        if ($ipAddress === '127.0.0.1' || $ipAddress === 'unknown') {
            return null;
        }

        // In production, use a service like MaxMind or IP-API
        return [
            'city' => 'Unknown City',
            'country' => 'Unknown Country',
            'lat' => 0.0,
            'lon' => 0.0,
        ];
    }

    /**
     * Parse device information from user agent.
     */
    private function parseDeviceInfo(string $userAgent): string
    {
        // TODO: Use a proper user agent parser
        $agent = mb_strtolower($userAgent);

        if (preg_match('/\((.*?)\)/', $userAgent, $matches)) {
            $platform = $matches[1];

            if (str_contains($agent, 'mobile')) {
                return "Mobile Device ($platform)";
            }
            if (str_contains($agent, 'tablet')) {
                return "Tablet ($platform)";
            }

            return "Desktop ($platform)";
        }

        return 'Unknown Device';
    }

    /**
     * Get the last successful login for a user.
     */
    private function getLastSuccessfulLogin(User $user): ?LoginAttempt
    {
        return LoginAttempt::where('user_id', $user->id)
            ->where('successful', true)
            ->orderBy('created_at', 'desc')
            ->skip(1) // Skip the current login
            ->first();
    }

    /**
     * Calculate distance between two coordinates in kilometers.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($lat2 - $lat1);
        $lonDiff = deg2rad($lon2 - $lon1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Update login history for pattern detection.
     */
    private function updateLoginHistory(User $user, string $ipAddress, string $userAgent): void
    {
        $cacheKey = "user:{$user->id}:login_history";
        $history = Cache::get($cacheKey, []);

        // Add new login
        $history[] = [
            'ip' => $ipAddress,
            'user_agent' => $userAgent,
            'timestamp' => now()->timestamp,
        ];

        // Keep only last 20 logins
        $history = array_slice($history, -20);

        Cache::put($cacheKey, $history, now()->addMonths(3));
    }
}
