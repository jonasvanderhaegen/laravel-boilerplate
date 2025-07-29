<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Modules\ClassicAuth\Events\LoginAttempted;
use Modules\ClassicAuth\Models\LoginAttempt;

test('login attempt tracking works correctly', function () {
    Event::fake();
    
    $user = User::factory()->create();
    
    // Test successful login
    $attempt = LoginAttempt::logSuccess($user, '192.168.1.1', 'Mozilla/5.0');
    
    expect($attempt)->toBeInstanceOf(LoginAttempt::class)
        ->and($attempt->successful)->toBeTrue()
        ->and($attempt->user_id)->toBe($user->id);
    
    Event::assertDispatched(LoginAttempted::class);
});

test('failed login attempts are tracked', function () {
    Event::fake();
    
    $attempt = LoginAttempt::logFailure(
        'test@example.com',
        '192.168.1.1',
        'Mozilla/5.0',
        LoginAttempt::FAILURE_INVALID_CREDENTIALS
    );
    
    expect($attempt->successful)->toBeFalse()
        ->and($attempt->failure_reason)->toBe(LoginAttempt::FAILURE_INVALID_CREDENTIALS);
    
    Event::assertDispatched(LoginAttempted::class);
});

test('login attempt scopes work correctly', function () {
    // Create various login attempts
    LoginAttempt::factory()->count(3)->successful()->create();
    LoginAttempt::factory()->count(2)->failed()->create();
    LoginAttempt::factory()->create(['ip_address' => '10.0.0.1']);
    LoginAttempt::factory()->create(['email' => 'specific@example.com']);
    
    expect(LoginAttempt::successful()->count())->toBe(4) // 3 successful + 1 from previous tests if any
        ->and(LoginAttempt::failed()->count())->toBe(3) // 2 failed + 1 from previous tests if any
        ->and(LoginAttempt::byIp('10.0.0.1')->count())->toBe(1)
        ->and(LoginAttempt::byEmail('specific@example.com')->count())->toBe(1);
});
