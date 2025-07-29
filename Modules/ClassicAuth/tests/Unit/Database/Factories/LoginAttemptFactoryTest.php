<?php

declare(strict_types=1);

use App\Models\User;
use Modules\ClassicAuth\Database\Factories\LoginAttemptFactory;
use Modules\ClassicAuth\Models\LoginAttempt;

test('factory creates login attempt with default state', function () {
    $attempt = LoginAttempt::factory()->create();
    
    expect($attempt)->toBeInstanceOf(LoginAttempt::class)
        ->and($attempt->email)->not->toBeNull()
        ->and($attempt->ip_address)->not->toBeNull()
        ->and($attempt->user_agent)->not->toBeNull()
        ->and($attempt->attempted_at)->not->toBeNull();
});

test('factory creates successful login attempt', function () {
    $attempt = LoginAttempt::factory()->successful()->create();
    
    expect($attempt->successful)->toBeTrue()
        ->and($attempt->failure_reason)->toBeNull()
        ->and($attempt->user_id)->not->toBeNull();
});

test('factory creates failed login attempt', function () {
    $attempt = LoginAttempt::factory()->failed()->create();
    
    expect($attempt->successful)->toBeFalse()
        ->and($attempt->failure_reason)->toBe(LoginAttempt::FAILURE_INVALID_CREDENTIALS);
});

test('factory creates failed login attempt with specific reason', function () {
    $attempt = LoginAttempt::factory()->failed(LoginAttempt::FAILURE_RATE_LIMITED)->create();
    
    expect($attempt->successful)->toBeFalse()
        ->and($attempt->failure_reason)->toBe(LoginAttempt::FAILURE_RATE_LIMITED);
});

test('factory creates login attempt from specific IP', function () {
    $ip = '192.168.1.100';
    $attempt = LoginAttempt::factory()->fromIp($ip)->create();
    
    expect($attempt->ip_address)->toBe($ip);
});

test('factory creates login attempt for specific email', function () {
    $email = 'test@example.com';
    $attempt = LoginAttempt::factory()->forEmail($email)->create();
    
    expect($attempt->email)->toBe($email);
});

test('factory creates login attempt for specific user', function () {
    $user = User::factory()->create(['email' => 'user@test.com']);
    $attempt = LoginAttempt::factory()->forUser($user)->create();
    
    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->email)->toBe('user@test.com');
});

test('factory creates login attempt at specific time', function () {
    $time = now()->subDays(5);
    $attempt = LoginAttempt::factory()->attemptedAt($time)->create();
    
    expect($attempt->attempted_at->timestamp)->toBe($time->timestamp);
});

test('factory attemptedAt closure is executed', function () {
    $time = now()->subDays(5);
    $factory = LoginAttempt::factory()->attemptedAt($time);
    
    // Create multiple instances to ensure closure is called
    $attempt1 = $factory->create();
    $attempt2 = $factory->create();
    
    expect($attempt1->attempted_at->timestamp)->toBe($time->timestamp)
        ->and($attempt2->attempted_at->timestamp)->toBe($time->timestamp);
});

test('factory chains multiple states', function () {
    $user = User::factory()->create();
    $ip = '10.0.0.1';
    $time = now()->subHours(2);
    
    $attempt = LoginAttempt::factory()
        ->successful()
        ->forUser($user)
        ->fromIp($ip)
        ->attemptedAt($time)
        ->create();
    
    expect($attempt->successful)->toBeTrue()
        ->and($attempt->user_id)->toBe($user->id)
        ->and($attempt->ip_address)->toBe($ip)
        ->and($attempt->attempted_at->timestamp)->toBe($time->timestamp);
});

test('factory has correct model property', function () {
    $factory = new LoginAttemptFactory();
    
    expect($factory->modelName())->toBe(LoginAttempt::class);
});
