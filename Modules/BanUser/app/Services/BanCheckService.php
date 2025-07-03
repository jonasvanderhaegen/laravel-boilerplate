<?php

declare(strict_types=1);

namespace Modules\BanUser\Services;

use Modules\BanUser\Models\BannedUser;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

/**
 * Service for checking if users are banned.
 */
final class BanCheckService
{
    private const CACHE_PREFIX = 'ban_check:';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Check if an email is banned.
     */
    public function isEmailBanned(string $email): bool
    {
        $cacheKey = self::CACHE_PREFIX . 'email:' . md5(strtolower($email));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($email) {
            return BannedUser::active()
                ->where('email', strtolower($email))
                ->exists();
        });
    }

    /**
     * Check if an IP address is banned.
     */
    public function isIpBanned(string $ipAddress): bool
    {
        $cacheKey = self::CACHE_PREFIX . 'ip:' . md5($ipAddress);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($ipAddress) {
            return BannedUser::active()
                ->where('ip_address', $ipAddress)
                ->exists();
        });
    }

    /**
     * Check if a user is banned by ID.
     */
    public function isUserBanned(int $userId): bool
    {
        $cacheKey = self::CACHE_PREFIX . 'user:' . $userId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            return BannedUser::active()
                ->where('user_id', $userId)
                ->exists();
        });
    }

    /**
     * Check if credentials are banned (email or IP).
     */
    public function areCredentialsBanned(string $email, ?string $ipAddress = null): bool
    {
        // Check email ban
        if ($this->isEmailBanned($email)) {
            return true;
        }

        // Check IP ban if provided
        if ($ipAddress && $this->isIpBanned($ipAddress)) {
            return true;
        }

        return false;
    }

    /**
     * Get active ban details for an email.
     */
    public function getBanDetails(string $email): ?BannedUser
    {
        return BannedUser::active()
            ->where('email', strtolower($email))
            ->latest('banned_at')
            ->first();
    }

    /**
     * Get active ban details for a user.
     */
    public function getUserBanDetails(User $user): ?BannedUser
    {
        return BannedUser::active()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('email', $user->email);
            })
            ->latest('banned_at')
            ->first();
    }

    /**
     * Clear ban cache for an email.
     */
    public function clearEmailCache(string $email): void
    {
        Cache::forget(self::CACHE_PREFIX . 'email:' . md5(strtolower($email)));
    }

    /**
     * Clear ban cache for an IP.
     */
    public function clearIpCache(string $ipAddress): void
    {
        Cache::forget(self::CACHE_PREFIX . 'ip:' . md5($ipAddress));
    }

    /**
     * Clear ban cache for a user.
     */
    public function clearUserCache(int $userId): void
    {
        Cache::forget(self::CACHE_PREFIX . 'user:' . $userId);
    }

    /**
     * Clear all ban caches.
     */
    public function clearAllCache(): void
    {
        // This is a simplified version. In production, you might want to use tags
        // or track all cache keys to clear them more efficiently
        Cache::flush();
    }

    /**
     * Ban a user.
     */
    public function banUser(array $data): BannedUser
    {
        $ban = BannedUser::createFromReport($data);

        // Clear relevant caches
        if ($ban->email) {
            $this->clearEmailCache($ban->email);
        }
        if ($ban->ip_address) {
            $this->clearIpCache($ban->ip_address);
        }
        if ($ban->user_id) {
            $this->clearUserCache($ban->user_id);
        }

        return $ban;
    }

    /**
     * Process expired bans.
     */
    public function processExpiredBans(): int
    {
        $expiredBans = BannedUser::expired()->get();
        $count = 0;

        foreach ($expiredBans as $ban) {
            $ban->lift();
            
            // Clear caches
            if ($ban->email) {
                $this->clearEmailCache($ban->email);
            }
            if ($ban->ip_address) {
                $this->clearIpCache($ban->ip_address);
            }
            if ($ban->user_id) {
                $this->clearUserCache($ban->user_id);
            }
            
            $count++;
        }

        return $count;
    }
}
