<?php

declare(strict_types=1);

namespace Modules\BanUser\Concerns;

use Modules\BanUser\Models\BannedUser;
use Modules\BanUser\Services\BanCheckService;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait for User model to check ban status.
 */
trait HasBanChecks
{
    /**
     * Get the ban check service.
     */
    protected function banCheckService(): BanCheckService
    {
        return app(BanCheckService::class);
    }

    /**
     * Relationship to banned records.
     */
    public function bans(): HasMany
    {
        return $this->hasMany(BannedUser::class, 'user_id');
    }

    /**
     * Get active bans.
     */
    public function activeBans(): HasMany
    {
        return $this->bans()->active();
    }

    /**
     * Check if the user is banned.
     */
    public function isBanned(): bool
    {
        return $this->banCheckService()->isUserBanned($this->id);
    }

    /**
     * Get the current active ban.
     */
    public function getCurrentBan(): ?BannedUser
    {
        return $this->activeBans()->latest('banned_at')->first();
    }

    /**
     * Ban this user.
     */
    public function ban(string $reason, array $options = []): BannedUser
    {
        return $this->banCheckService()->banUser([
            'user_id' => $this->id,
            'email' => $this->email,
            'ip_address' => $options['ip_address'] ?? request()->ip(),
            'reason' => $reason,
            'details' => $options['details'] ?? null,
            'banned_by' => $options['banned_by'] ?? 'system',
            'expires_at' => $options['expires_at'] ?? null,
        ]);
    }

    /**
     * Lift all active bans for this user.
     */
    public function unban(): int
    {
        $count = 0;
        foreach ($this->activeBans as $ban) {
            if ($ban->lift()) {
                $count++;
            }
        }

        // Clear cache
        $this->banCheckService()->clearUserCache($this->id);
        $this->banCheckService()->clearEmailCache($this->email);

        return $count;
    }
}
