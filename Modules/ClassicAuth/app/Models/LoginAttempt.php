<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Models;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ClassicAuth\Events\LoginAttempted;

/**
 * Login attempt tracking model.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property string $ip_address
 * @property string $user_agent
 * @property bool $successful
 * @property string|null $failure_reason
 * @property DateTimeInterface $attempted_at
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property-read User|null $user
 */
final class LoginAttempt extends Model
{
    /**
     * Failure reason constants.
     */
    public const FAILURE_INVALID_CREDENTIALS = 'invalid_credentials';

    public const FAILURE_RATE_LIMITED = 'rate_limited';

    public const FAILURE_ACCOUNT_DISABLED = 'account_disabled';

    public const FAILURE_EMAIL_NOT_VERIFIED = 'email_not_verified';

    /**
     * The table associated with the model.
     */
    protected $table = 'login_attempts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'attempted_at',
    ];

    /**
     * Log a successful login attempt.
     */
    public static function logSuccess(User $user, string $ipAddress, string $userAgent): self
    {
        $attempt = self::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'successful' => true,
            'failure_reason' => null,
            'attempted_at' => now(),
        ]);

        event(new LoginAttempted($attempt));

        return $attempt;
    }

    /**
     * Log a failed login attempt.
     */
    public static function logFailure(
        string $email,
        string $ipAddress,
        string $userAgent,
        string $failureReason
    ): self {
        // Try to find the user by email for tracking
        $user = User::where('email', $email)->first();

        $attempt = self::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'successful' => false,
            'failure_reason' => $failureReason,
            'attempted_at' => now(),
        ]);

        event(new LoginAttempted($attempt));

        return $attempt;
    }

    /**
     * Get the user associated with the login attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get recent attempts.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function recent($query, int $days = 7)
    {
        return $query->where('attempted_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get failed attempts.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function failed($query)
    {
        return $query->where('successful', false);
    }

    /**
     * Scope to get successful attempts.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function successful($query)
    {
        return $query->where('successful', true);
    }

    /**
     * Scope to get attempts by IP.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byIp($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope to get attempts by email.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function byEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'attempted_at' => 'datetime',
        ];
    }
}
