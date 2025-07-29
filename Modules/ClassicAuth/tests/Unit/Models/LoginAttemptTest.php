<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Event;
use Modules\ClassicAuth\Database\Factories\LoginAttemptFactory;
use Modules\ClassicAuth\Events\LoginAttempted;
use Modules\ClassicAuth\Models\LoginAttempt;

beforeEach(function () {
    Event::fake();
    LoginAttempt::query()->delete();
});

describe('LoginAttempt Model', function () {
    it('has correct table name', function () {
        $attempt = new LoginAttempt();
        
        expect($attempt->getTable())->toBe('login_attempts');
    });
    
    it('has correct fillable attributes', function () {
        $attempt = new LoginAttempt();
        
        expect($attempt->getFillable())->toBe([
            'user_id',
            'email',
            'ip_address',
            'user_agent',
            'successful',
            'failure_reason',
            'attempted_at',
        ]);
    });
    
    it('casts attributes correctly', function () {
        $attempt = new LoginAttempt();
        $casts = $attempt->getCasts();
        
        expect($casts)->toHaveKey('successful', 'boolean')
            ->toHaveKey('attempted_at', 'datetime');
    });
    
    it('has user relationship', function () {
        $attempt = new LoginAttempt();
        
        expect($attempt->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
            ->and($attempt->user()->getRelated())->toBeInstanceOf(User::class);
    });
});

describe('Login Attempt Logging', function () {
    it('logs successful login attempt', function () {
        $user = User::factory()->create();
        
        $attempt = LoginAttempt::logSuccess($user, '192.168.1.1', 'Mozilla/5.0');
        
        expect($attempt)->toBeInstanceOf(LoginAttempt::class)
            ->and($attempt->user_id)->toBe($user->id)
            ->and($attempt->email)->toBe($user->email)
            ->and($attempt->ip_address)->toBe('192.168.1.1')
            ->and($attempt->user_agent)->toBe('Mozilla/5.0')
            ->and($attempt->successful)->toBeTrue()
            ->and($attempt->failure_reason)->toBeNull()
            ->and($attempt->attempted_at)->not->toBeNull();
        
        Event::assertDispatched(LoginAttempted::class);
    });
    
    it('logs failed login attempt with user', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $attempt = LoginAttempt::logFailure(
            'test@example.com',
            '192.168.1.1',
            'Mozilla/5.0',
            LoginAttempt::FAILURE_INVALID_CREDENTIALS
        );
        
        expect($attempt)->toBeInstanceOf(LoginAttempt::class)
            ->and($attempt->user_id)->toBe($user->id)
            ->and($attempt->email)->toBe('test@example.com')
            ->and($attempt->ip_address)->toBe('192.168.1.1')
            ->and($attempt->user_agent)->toBe('Mozilla/5.0')
            ->and($attempt->successful)->toBeFalse()
            ->and($attempt->failure_reason)->toBe(LoginAttempt::FAILURE_INVALID_CREDENTIALS);
        
        Event::assertDispatched(LoginAttempted::class);
    });
    
    it('logs failed login attempt without user', function () {
        $attempt = LoginAttempt::logFailure(
            'nonexistent@example.com',
            '192.168.1.1',
            'Mozilla/5.0',
            LoginAttempt::FAILURE_INVALID_CREDENTIALS
        );
        
        expect($attempt->user_id)->toBeNull()
            ->and($attempt->email)->toBe('nonexistent@example.com');
        
        Event::assertDispatched(LoginAttempted::class);
    });
});

describe('Query Scopes', function () {
    it('recent scope filters by days', function () {
        // Create attempts at different times
        LoginAttempt::factory()->count(3)->create(['attempted_at' => now()->subDays(5)]);
        LoginAttempt::factory()->count(2)->create(['attempted_at' => now()->subDays(10)]);
        
        // Test with default parameter
        $recent = LoginAttempt::recent()->get();
        expect($recent)->toHaveCount(3);
        
        // Test with custom parameter
        $recentCustom = LoginAttempt::recent(4)->get();
        expect($recentCustom)->toHaveCount(0);
        
        // Test with parameter that includes all
        $recentAll = LoginAttempt::recent(15)->get();
        expect($recentAll)->toHaveCount(5);
    });
    
    it('failed scope filters unsuccessful attempts', function () {
        LoginAttempt::factory()->count(3)->successful()->create();
        LoginAttempt::factory()->count(2)->failed()->create();
        
        $failed = LoginAttempt::failed()->get();
        expect($failed)->toHaveCount(2);
        
        // Verify all are failed
        foreach ($failed as $attempt) {
            expect($attempt->successful)->toBeFalse();
        }
    });
    
    it('successful scope filters successful attempts', function () {
        LoginAttempt::factory()->count(3)->successful()->create();
        LoginAttempt::factory()->count(2)->failed()->create();
        
        $successful = LoginAttempt::successful()->get();
        expect($successful)->toHaveCount(3);
        
        // Verify all are successful
        foreach ($successful as $attempt) {
            expect($attempt->successful)->toBeTrue();
        }
    });
    
    it('byIp scope filters by IP address', function () {
        LoginAttempt::factory()->count(2)->create(['ip_address' => '10.0.0.1']);
        LoginAttempt::factory()->count(3)->create(['ip_address' => '10.0.0.2']);
        
        $byIp1 = LoginAttempt::byIp('10.0.0.1')->get();
        expect($byIp1)->toHaveCount(2);
        
        $byIp2 = LoginAttempt::byIp('10.0.0.2')->get();
        expect($byIp2)->toHaveCount(3);
        
        $byIpNone = LoginAttempt::byIp('10.0.0.3')->get();
        expect($byIpNone)->toHaveCount(0);
    });
    
    it('byEmail scope filters by email', function () {
        LoginAttempt::factory()->count(2)->create(['email' => 'user1@example.com']);
        LoginAttempt::factory()->count(3)->create(['email' => 'user2@example.com']);
        
        $byEmail1 = LoginAttempt::byEmail('user1@example.com')->get();
        expect($byEmail1)->toHaveCount(2);
        
        $byEmail2 = LoginAttempt::byEmail('user2@example.com')->get();
        expect($byEmail2)->toHaveCount(3);
        
        $byEmailNone = LoginAttempt::byEmail('user3@example.com')->get();
        expect($byEmailNone)->toHaveCount(0);
    });
    
    it('scopes return query builder instances', function () {
        $model = new LoginAttempt();
        $query = $model->newQuery();
        
        expect($query->recent())->toBeInstanceOf(Builder::class);
        expect($query->recent(5))->toBeInstanceOf(Builder::class);
        expect($query->failed())->toBeInstanceOf(Builder::class);
        expect($query->successful())->toBeInstanceOf(Builder::class);
        expect($query->byIp('test'))->toBeInstanceOf(Builder::class);
        expect($query->byEmail('test'))->toBeInstanceOf(Builder::class);
    });
    
    it('chains multiple scopes correctly', function () {
        // Create specific test data
        LoginAttempt::factory()->create([
            'email' => 'test@example.com',
            'ip_address' => '192.168.1.100',
            'successful' => false,
            'attempted_at' => now()->subDays(2),
        ]);
        
        LoginAttempt::factory()->create([
            'email' => 'test@example.com',
            'ip_address' => '192.168.1.101',
            'successful' => false,
            'attempted_at' => now()->subDays(2),
        ]);
        
        // This should not be included (successful)
        LoginAttempt::factory()->create([
            'email' => 'test@example.com',
            'ip_address' => '192.168.1.100',
            'successful' => true,
            'attempted_at' => now()->subDays(2),
        ]);
        
        $attempts = LoginAttempt::recent()
            ->failed()
            ->byEmail('test@example.com')
            ->byIp('192.168.1.100')
            ->get();
        
        expect($attempts)->toHaveCount(1);
    });
});

describe('Factory Method', function () {
    it('returns correct factory instance', function () {
        $factory = LoginAttempt::factory();
        
        expect($factory)->toBeInstanceOf(LoginAttemptFactory::class);
    });
    
    it('newFactory method returns correct instance', function () {
        $model = new LoginAttempt();
        $reflection = new ReflectionClass($model);
        $method = $reflection->getMethod('newFactory');
        $method->setAccessible(true);
        
        $factory = $method->invoke($model);
        
        expect($factory)->toBeInstanceOf(LoginAttemptFactory::class);
    });
});

describe('Failure Reason Constants', function () {
    it('has correct failure reason constants', function () {
        expect(LoginAttempt::FAILURE_INVALID_CREDENTIALS)->toBe('invalid_credentials')
            ->and(LoginAttempt::FAILURE_RATE_LIMITED)->toBe('rate_limited')
            ->and(LoginAttempt::FAILURE_ACCOUNT_DISABLED)->toBe('account_disabled')
            ->and(LoginAttempt::FAILURE_EMAIL_NOT_VERIFIED)->toBe('email_not_verified');
    });
});
