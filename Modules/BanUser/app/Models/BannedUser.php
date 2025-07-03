<?php

declare(strict_types=1);

namespace Modules\BanUser\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * BannedUser Model
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string|null $ip_address
 * @property string $reason
 * @property string|null $details
 * @property string|null $banned_by
 * @property Carbon $banned_at
 * @property Carbon|null $expires_at
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 */
final class BannedUser extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'reason',
        'details',
        'banned_by',
        'banned_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (BannedUser $ban) {
            if (!$ban->banned_at) {
                $ban->banned_at = now();
            }
        });
    }

    /**
     * Relationship to User model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active bans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for expired bans.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Check if the ban is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the ban is permanent.
     */
    public function isPermanent(): bool
    {
        return is_null($this->expires_at);
    }

    /**
     * Get the remaining ban duration.
     */
    public function getRemainingDuration(): ?string
    {
        if ($this->isPermanent()) {
            return 'Permanent';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        return $this->expires_at?->diffForHumans();
    }

    /**
     * Lift the ban.
     */
    public function lift(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Create a ban from report data.
     */
    public static function createFromReport(array $data): self
    {
        $user = User::where('email', $data['email'])->first();

        return self::create([
            'user_id' => $user?->id,
            'email' => $data['email'],
            'ip_address' => $data['ip_address'] ?? null,
            'reason' => $data['reason'] ?? 'User reported',
            'details' => $data['details'] ?? null,
            'banned_by' => $data['banned_by'] ?? 'system',
            'banned_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);
    }
}
