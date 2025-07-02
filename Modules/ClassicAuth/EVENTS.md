# ClassicAuth Events Documentation

This document describes all the events dispatched by the ClassicAuth module and how to use them.

## Overview

The ClassicAuth module dispatches various events throughout the authentication lifecycle. These events allow you to:

- Track authentication metrics
- Audit security events
- Send notifications
- Implement custom business logic
- Monitor suspicious activities

## Event Categories

### 1. Login Events

#### `LoginAttempted`
Fired when any login attempt is made (before validation).
```php
event(new LoginAttempted($loginAttempt));
```

#### `LoginSucceeded`
Fired when a user successfully logs in.
```php
event(new LoginSucceeded($user, $ipAddress, $userAgent, $remember));
```

#### `LoginFailed`
Fired when a login attempt fails.
```php
event(new LoginFailed($email, $ipAddress, $userAgent, $failureReason));
```
Failure reasons: `invalid_credentials`, `rate_limited`

#### `UserLoggedOut`
Fired when a user logs out.
```php
event(new UserLoggedOut($user, $ipAddress, $userAgent, $sessionId));
```

### 2. Registration Events

#### `UserRegistering`
Fired before user registration is processed.
```php
event(new UserRegistering($credentials, $ipAddress, $userAgent));
```

#### `UserRegistered`
Fired after successful user registration.
```php
event(new UserRegistered($user, $ipAddress, $userAgent, $autoLoggedIn));
```

#### `RegistrationFailed`
Fired when registration fails.
```php
event(new RegistrationFailed($email, $ipAddress, $userAgent, $failureReason));
```
Failure reasons: `email_taken`, `rate_limited`

### 3. Password Reset Events

#### `PasswordResetRequested`
Fired when a password reset is requested.
```php
event(new PasswordResetRequested($email, $ipAddress, $userAgent));
```

#### `PasswordResetLinkSent`
Fired when a password reset link is sent (or attempted).
```php
event(new PasswordResetLinkSent($email, $ipAddress, $actuallySent));
```

#### `PasswordResetCompleted`
Fired when a password is successfully reset.
```php
event(new PasswordResetCompleted($user, $ipAddress, $userAgent, $autoLoggedIn));
```

#### `PasswordResetFailed`
Fired when a password reset fails.
```php
event(new PasswordResetFailed($email, $ipAddress, $userAgent, $failureReason));
```
Failure reasons: `invalid_token`, `invalid_user`, `rate_limited`

### 4. Email Verification Events

#### `EmailVerificationRequested`
Fired when email verification is requested.
```php
event(new EmailVerificationRequested($user, $ipAddress, $userAgent));
```

#### `EmailVerificationLinkSent`
Fired when an email verification link is sent.
```php
event(new EmailVerificationLinkSent($user, $ipAddress));
```

#### `EmailVerificationCompleted`
Fired when email verification is completed.
```php
event(new EmailVerificationCompleted($user, $ipAddress, $userAgent));
```

### 5. Security Events

#### `SuspiciousActivityDetected`
Fired when suspicious activity is detected.
```php
event(new SuspiciousActivityDetected($activityType, $ipAddress, $email, $metadata));
```
Activity types: `brute_force`, `credential_stuffing`, `multiple_ips`

#### `TooManyFailedAttempts`
Fired when rate limiting is triggered.
```php
event(new TooManyFailedAttempts($attemptType, $identifier, $attemptCount, $timeWindow, $ipAddress));
```

## Built-in Listeners

The module includes several built-in listeners:

### 1. `LogLoginAttempt`
Logs all login attempts to the database.

### 2. `TrackAuthenticationMetrics`
Tracks authentication metrics in cache for analytics.

### 3. `AuditAuthenticationEvents`
Logs all authentication events for audit purposes.

### 4. `SendWelcomeEmail`
Sends welcome emails after registration (queued).

### 5. `NotifySuspiciousActivity`
Handles suspicious activity notifications.

### 6. `CleanupAuthenticationData`
Periodically cleans up old authentication data.

## Creating Custom Listeners

To create a custom listener for any event:

1. Create a listener class:
```php
namespace App\Listeners;

use Modules\ClassicAuth\Events\Login\LoginSucceeded;

class NotifyAdminOfLogin
{
    public function handle(LoginSucceeded $event)
    {
        // Your custom logic here
        if ($event->user->is_admin) {
            // Send notification
        }
    }
}
```

2. Register it in your `EventServiceProvider`:
```php
protected $listen = [
    \Modules\ClassicAuth\Events\Login\LoginSucceeded::class => [
        \App\Listeners\NotifyAdminOfLogin::class,
    ],
];
```

## Event Data

All events implement a `toArray()` method that returns event data suitable for logging:

```php
$event->toArray(); // Returns array with all event data
```

## Configuration

Event behavior can be configured in `config/classicauth.php`:

- **Logging**: Configure log channel and level
- **Notifications**: Configure admin email and thresholds
- **Tracking**: Enable/disable event tracking

## Security Considerations

1. **Email Enumeration**: Password reset events always fire to prevent email enumeration
2. **Timing Attacks**: All authentication actions use timing attack prevention
3. **Rate Limiting**: Events are dispatched when rate limits are exceeded
4. **Audit Trail**: All events can be logged for security auditing

## Examples

### Listen for Failed Logins
```php
Event::listen(LoginFailed::class, function ($event) {
    if ($event->failureReason === 'invalid_credentials') {
        // Track failed login for this email
    }
});
```

### Monitor Suspicious Activity
```php
Event::listen(SuspiciousActivityDetected::class, function ($event) {
    if ($event->isBruteForce()) {
        // Block IP address
        // Send security alert
    }
});
```

### Welcome New Users
```php
Event::listen(UserRegistered::class, function ($event) {
    // Send welcome email
    // Add to mailing list
    // Create user profile
});
```

## Testing Events

When testing, you can assert that events were dispatched:

```php
Event::fake();

// Perform authentication action

Event::assertDispatched(LoginSucceeded::class, function ($event) {
    return $event->user->email === 'test@example.com';
});
```
