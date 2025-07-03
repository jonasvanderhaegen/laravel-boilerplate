# BanUser Module

A comprehensive user banning system that integrates with the ClassicAuth module to prevent banned users from logging in, registering, or resetting their passwords.

## Features

- Ban users by email address
- Ban users by IP address
- Temporary or permanent bans
- Automatic ban expiration processing
- Integration with ClassicAuth for login/registration/password reset blocking
- API endpoints for reporting users
- Event logging for ban activities
- Middleware for checking authenticated users

## Installation

1. Run migrations:
```bash
php artisan migrate
```

2. The module automatically integrates with ClassicAuth if it's installed.

## Usage

### Banning Users via API

Report a user (creates a ban):
```bash
POST /api/v1/bans/report
{
    "email": "user@example.com",
    "reason": "Spam/Abuse",
    "details": "Multiple spam reports",
    "duration_hours": 24  // Optional, omit for permanent ban
}
```

### Checking Ban Status

Check if an email is banned:
```bash
POST /api/v1/bans/check
{
    "email": "user@example.com"
}
```

### Lifting Bans

Lift a ban:
```bash
POST /api/v1/bans/lift
{
    "email": "user@example.com"
}
```

### Using in Code

```php
use Modules\BanUser\Services\BanCheckService;

// Check if email is banned
$banService = app(BanCheckService::class);
$isBanned = $banService->isEmailBanned('user@example.com');

// Ban a user programmatically
$ban = $banService->banUser([
    'email' => 'user@example.com',
    'reason' => 'Terms violation',
    'banned_by' => 'admin',
    'expires_at' => now()->addDays(7), // Optional
]);

// Using with User model
$user = User::find(1);
if ($user->isBanned()) {
    $ban = $user->getCurrentBan();
    echo "User is banned: " . $ban->reason;
}

// Ban a user through the model
$user->ban('Repeated violations', [
    'details' => 'Multiple reports of harassment',
    'expires_at' => now()->addDays(30),
]);

// Unban a user
$user->unban();
```

### Middleware

Apply the ban check middleware to routes:
```php
Route::middleware(['auth', 'check.banned'])->group(function () {
    // Protected routes
});
```

Or apply globally by uncommenting the line in `PrimaryServiceProvider`:
```php
$router->pushMiddlewareToGroup('web', CheckBanned::class);
```

## Console Commands

Process expired bans manually:
```bash
php artisan bans:process-expired
```

This command runs automatically every hour via the scheduler.

## Events

The module dispatches the following events:

- `UserBanned`: When a user is banned
- `BannedUserAttempted`: When a banned user attempts to login/register/reset password

## How It Works

1. When a user is reported via the API, a ban record is created in the `banned_users` table
2. The ClassicAuth integration automatically checks for bans during:
   - Login attempts
   - User registration
   - Password reset requests
3. Banned users receive appropriate error messages without revealing specific ban details
4. The system checks both email and IP addresses for bans
5. Temporary bans are automatically lifted when they expire

## Security Considerations

- Ban checks are cached for 5 minutes to improve performance
- Password reset requests for banned users appear to succeed (to prevent email enumeration)
- All ban activities are logged
- IP bans should be used carefully to avoid blocking legitimate users on shared IPs

## Database Schema

The `banned_users` table stores:
- `user_id`: Optional reference to users table
- `email`: The banned email address
- `ip_address`: Optional IP address
- `reason`: Ban reason
- `details`: Additional details
- `banned_by`: Who initiated the ban
- `banned_at`: When the ban started
- `expires_at`: When the ban expires (NULL for permanent)
- `is_active`: Whether the ban is currently active
