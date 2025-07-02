# ClassicAuth Login Refactoring Summary

## Overview
Refactored the ClassicAuth Livewire login components to follow the action-driven pattern, separating business logic from UI concerns.

## Changes Made

### 1. Created Action Layer
- **`LoginUserAction`**: Encapsulates all authentication business logic
  - IP-based rate limiting
  - Email-based rate limiting  
  - Authentication attempt
  - Session management
  - Event dispatching
  - User state updates (last login timestamp, IP)
  - **Timing attack prevention using Laravel's Timebox**
    - Ensures consistent 300ms minimum execution time
    - Uses `returnEarly()` for successful logins only
    - Maintains full timing for failed attempts

### 2. Created Data Transfer Object
- **`LoginCredentials`**: Type-safe DTO for login data
  - Ensures email is lowercase and trimmed
  - Provides conversion methods for different use cases
  - Immutable design with readonly properties

### 3. Simplified LoginForm
- Removed business logic (authentication, rate limiting)
- Now only handles:
  - Input validation rules
  - Form state management
  - Creating DTO from form data
- Cleaner separation of concerns

### 4. Updated Login Component
- Delegates authentication to `LoginUserAction`
- Focuses on UI concerns:
  - Form presentation
  - Error handling and display
  - Navigation between auth pages
  - Password visibility toggle
- Uses dependency injection for the action

## Benefits

### 1. **Testability**
- Business logic in action is easily unit testable
- No need to mock Livewire internals
- Can test authentication logic in isolation

### 2. **Reusability**
- `LoginUserAction` can be used in:
  - API endpoints
  - Console commands
  - Other authentication flows
  - Queue jobs

### 3. **Maintainability**
- Clear separation of concerns
- Business logic is centralized
- Easier to modify authentication flow
- Type safety with DTO

### 4. **Consistency**
- Follows the same pattern as other actions in the codebase
- Predictable structure for developers

## File Structure
```
Modules/ClassicAuth/app/
├── Actions/
│   └── LoginUserAction.php          # Business logic
├── DataTransferObjects/
│   └── LoginCredentials.php         # Type-safe data structure
└── Livewire/
    ├── Components/
    │   └── Login.php               # UI component (updated)
    └── Forms/
        └── LoginForm.php           # Form validation (simplified)
```

## Usage Example

```php
// In Livewire component
public function submit(LoginUserAction $action): void
{
    try {
        $this->form->validate();
        $action->execute($this->form->getCredentials());
        $this->handleSuccessfulAuthentication();
    } catch (TooManyRequestsException $e) {
        // Handle rate limiting
    } catch (ValidationException $e) {
        // Handle validation errors
    }
}

// In API controller
public function login(Request $request, LoginUserAction $action)
{
    $credentials = LoginCredentials::fromArray($request->validated());
    $user = $action->execute($credentials);
    
    return response()->json([
        'user' => $user,
        'token' => $user->createToken('api')->plainTextToken,
    ]);
}
```

## Migration Notes

1. The action assumes the User model has `last_login_at` and `last_login_ip` fields
2. Rate limiting keys remain the same for backward compatibility
3. All existing functionality is preserved
4. No changes required to views or routes

## New Features Added

### 1. LoginResult DTO
- Returns structured data from login action
- Contains user, intended URL, IP, user agent, and timestamp
- Provides session data storage methods
- Type-safe response handling

### 2. Login Attempt Logging
- **LoginAttempt Model**: Tracks all login attempts
- Records successful and failed attempts
- Stores IP address, user agent, and failure reasons
- Provides query scopes for analysis

### 3. Event System
- **LoginAttempted Event**: Fired for all login attempts
- **LogLoginAttempt Listener**: Handles logging and security checks
- Detects suspicious activity patterns
- Can notify users of new location logins

## Configuration

The module includes a comprehensive configuration file at `config/classicauth.php`. You can publish it:
```bash
php artisan vendor:publish --tag=classicauth-config
```

### Key Configuration Options:

1. **Default Routes**
   - `AUTH_LOGIN_REDIRECT`: Where to redirect after login (default: 'dashboard')
   - `AUTH_LOGOUT_REDIRECT`: Where to redirect after logout (default: '/')

2. **Rate Limiting**
   - `AUTH_IP_MAX_ATTEMPTS`: Max attempts per IP (default: 5)
   - `AUTH_IP_DECAY_SECONDS`: IP rate limit window (default: 60)
   - `AUTH_EMAIL_MAX_ATTEMPTS`: Max attempts per email (default: 15)
   - `AUTH_EMAIL_DECAY_SECONDS`: Email rate limit window (default: 3600)

3. **Login Tracking**
   - `AUTH_TRACKING_ENABLED`: Enable/disable tracking (default: true)
   - `AUTH_IP_FAILURES_THRESHOLD`: Suspicious IP threshold (default: 10)
   - `AUTH_EMAIL_IPS_THRESHOLD`: Suspicious email threshold (default: 5)
   - `AUTH_TRACKING_RETENTION_DAYS`: How long to keep records (default: 90)

4. **Security**
   - `AUTH_MIN_TIME_MS`: Minimum authentication time in milliseconds (default: 300)

## Database Migrations Required

Run the migrations to create the required tables:
```bash
# Run all ClassicAuth module migrations
php artisan module:migrate ClassicAuth

# Or run specific migrations
php artisan migrate --path=Modules/ClassicAuth/database/migrations/
```

This will create:
- `login_attempts` table for tracking login attempts
- Add `last_login_at` and `last_login_ip` columns to the users table

## Implementation Details

### Dependency Injection
The `LoginUserAction` is registered in the service container with its Timebox dependency:
```php
// In PrimaryServiceProvider
$this->app->bind(LoginUserAction::class, function ($app) {
    return new LoginUserAction(
        $app->make(Timebox::class)
    );
});
```

This allows Livewire to automatically inject the properly configured action.

## Usage Examples

### Analyzing Login Attempts
```php
// Get recent failed attempts
$failedAttempts = LoginAttempt::failed()->recent(7)->get();

// Get attempts by IP
$ipAttempts = LoginAttempt::byIp('192.168.1.1')->get();

// Get successful logins for a user
$userLogins = LoginAttempt::successful()
    ->where('user_id', $userId)
    ->latest('attempted_at')
    ->get();
```

### Event Listener Registration
The event listener is automatically registered in the module's EventServiceProvider.

### Timing Attack Prevention Middleware
Use the middleware for endpoints that need timing protection but don't use Timebox internally:

```php
// Protect password reset endpoint
Route::post('/password/email', SendPasswordResetController::class)
    ->middleware('classicauth.timing:300');

// Protect email availability checks
Route::post('/check-email', CheckEmailController::class)
    ->middleware('classicauth.timing:200');

// Protect API token validation
Route::post('/api/validate-token', ValidateTokenController::class)
    ->middleware('classicauth.timing:250');

// Register middleware in Kernel.php
protected $middlewareAliases = [
    'classicauth.timing' => \Modules\ClassicAuth\Http\Middleware\EnforceMinimumExecutionTime::class,
];
```

**Note**: Don't use this middleware on routes that already use Timebox internally (like login), as it would add unnecessary delay.

### Cleanup Command
To clean up old login attempts based on retention policy:
```bash
# Using config retention days
php artisan classicauth:cleanup-login-attempts

# Override retention days
php artisan classicauth:cleanup-login-attempts --days=30

# Preview what would be deleted
php artisan classicauth:cleanup-login-attempts --dry-run
```

Schedule in `app/Console/Kernel.php`:
```php
$schedule->command('classicauth:cleanup-login-attempts')->daily();
```

## Security Features

1. **Timing Attack Prevention with Laravel Timebox**
   - Uses Laravel's built-in Timebox for constant-time execution
   - Default 300ms minimum execution time (configurable)
   - Failed logins always take full minimum time
   - Successful logins can return early for better UX
   - Protects against user enumeration attacks
   - Handles exceptions while maintaining timing consistency

### When to Use Timebox vs Middleware

**Use Timebox when:**
- You need fine-grained control (like `returnEarly()` for success cases)
- The logic is complex with multiple code paths
- You're building reusable actions or services
- Example: Login, complex API authentication

**Use the Middleware when:**
- You need simple timing protection for an entire endpoint
- The endpoint always takes roughly the same time
- You don't need different timing for success/failure
- Example: Password reset, email checks, simple validations

2. **Suspicious Activity Detection**
   - Multiple failed attempts from same IP
   - Failed attempts from multiple IPs for same email
   - Configurable thresholds

3. **New Location Detection**
   - Tracks IPs used by each user
   - Can trigger notifications for new locations

4. **Comprehensive Logging**
   - All attempts logged with context
   - Separate security log channel for suspicious activity

## Future Improvements

1. Implement device fingerprinting
2. Add two-factor authentication support
3. Create corresponding `LogoutUserAction`
4. Add admin dashboard for login analytics
5. Implement automatic IP blocking for repeated failures
