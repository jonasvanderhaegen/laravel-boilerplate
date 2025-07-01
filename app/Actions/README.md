# Actions Directory

This directory contains all application-wide action classes that implement business logic.

## Structure
```
Actions/
├── User/
│   ├── CreateUserAction.php
│   ├── UpdateUserAction.php
│   ├── DeleteUserAction.php
│   └── SendWelcomeEmailAction.php
├── Auth/
│   ├── LoginAction.php
│   ├── LogoutAction.php
│   └── RefreshTokenAction.php
└── Shared/
    ├── SendNotificationAction.php
    └── LogActivityAction.php
```

## Action Pattern Rules

1. **Single Responsibility**: Each action should do one thing well
2. **Testable**: Actions should be easily unit testable
3. **Reusable**: Actions can be called from controllers, commands, jobs, etc.
4. **Type-Safe**: Use PHP types for parameters and returns
5. **Dependency Injection**: Inject dependencies via constructor

## Example Action

```php
<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Events\UserCreated;

class CreateUserAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new UserCreated($user));

        return $user;
    }
}
```

## Using Actions in Controllers

```php
public function store(Request $request, CreateUserAction $action)
{
    $user = $action->execute($request->validated());
    
    return new UserResource($user);
}
```
