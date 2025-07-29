<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Modules\ClassicAuth\Events\LoginAttempted;
use Modules\ClassicAuth\Listeners\LogLoginAttempt;
use Modules\ClassicAuth\Models\LoginAttempt;

beforeEach(function () {
    Config::set('classicauth.tracking.suspicious_activity', [
        'ip_failures_threshold' => 10,
        'ip_failures_window' => 3600,
        'email_ips_threshold' => 5,
        'email_ips_window' => 21600,
    ]);
    
    Log::spy();
    LoginAttempt::query()->delete();
});

describe('LogLoginAttempt Listener', function () {
    it('logs login attempts to auth channel', function () {
        $attempt = LoginAttempt::factory()->create([
            'successful' => true,
            'email' => 'test@example.com',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'failure_reason' => null,
        ]);
        
        $event = new LoginAttempted($attempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldHaveReceived('channel')->with('auth')->once();
        Log::shouldHaveReceived('info')
            ->with('Login attempt', \Mockery::type('array'))
            ->once();
    });
    
    // @codeCoverageIgnoreStart
    it('logs correct data structure', function () {
        $attempt = LoginAttempt::factory()->create([
            'successful' => false,
            'email' => 'test@example.com',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'failure_reason' => 'invalid_credentials',
            'attempted_at' => now(),
        ]);
        
        $event = new LoginAttempted($attempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($attempt) {
            return $message === 'Login attempt'
                && $context['successful'] === false
                && $context['email'] === 'test@example.com'
                && $context['ip_address'] === '192.168.1.1'
                && $context['user_agent'] === 'Mozilla/5.0'
                && $context['failure_reason'] === 'invalid_credentials'
                && isset($context['attempted_at']);
        })->once();
    });
    // @codeCoverageIgnoreEnd
    
    it('detects suspicious activity from multiple failed IP attempts', function () {
        // Create exactly threshold number of failures
        LoginAttempt::factory()->count(10)->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
            'attempted_at' => now(),
        ]);
        
        $newAttempt = LoginAttempt::factory()->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldHaveReceived('channel')->with('security')->once();
        Log::shouldHaveReceived('warning')
            ->with('Suspicious login activity detected', \Mockery::type('array'))
            ->once();
    });
    
    it('does not detect suspicious activity below threshold', function () {
        // Create below threshold
        LoginAttempt::factory()->count(9)->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
            'attempted_at' => now(),
        ]);
        
        $newAttempt = LoginAttempt::factory()->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('channel')->with('security');
    });
    
    it('detects suspicious activity from multiple IPs for same email', function () {
        $email = 'target@example.com';
        
        // Create failed attempts from exactly threshold IPs
        for ($i = 1; $i <= 5; $i++) {
            LoginAttempt::factory()->create([
                'email' => $email,
                'ip_address' => "192.168.1.{$i}",
                'successful' => false,
                'attempted_at' => now(),
            ]);
        }
        
        $newAttempt = LoginAttempt::factory()->create([
            'email' => $email,
            'ip_address' => '192.168.1.6',
            'successful' => false,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldHaveReceived('channel')->with('security')->once();
    });
    
    it('does not check multiple IPs for successful attempts', function () {
        $email = 'target@example.com';
        
        // Create failed attempts from multiple IPs
        for ($i = 1; $i <= 5; $i++) {
            LoginAttempt::factory()->create([
                'email' => $email,
                'ip_address' => "192.168.1.{$i}",
                'successful' => false,
                'attempted_at' => now(),
            ]);
        }
        
        // But the new attempt is successful
        $newAttempt = LoginAttempt::factory()->create([
            'email' => $email,
            'ip_address' => '192.168.1.6',
            'successful' => true,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('channel')->with('security');
    });
    
    it('detects login from new location', function () {
        $user = User::factory()->create();
        
        // Previous successful login from different IP
        LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'successful' => true,
        ]);
        
        // New login from different IP
        $newAttempt = LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.2',
            'successful' => true,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldHaveReceived('info')
            ->with('New location login detected', \Mockery::type('array'))
            ->once();
    });
    
    it('does not detect new location for repeated IP', function () {
        $user = User::factory()->create();
        
        // Previous successful login
        LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'successful' => true,
        ]);
        
        // New login from same IP
        $newAttempt = LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'successful' => true,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('info')
            ->with('New location login detected', \Mockery::type('array'));
    });
    
    it('does not check new location for failed attempts', function () {
        $user = User::factory()->create();
        
        $attempt = LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'successful' => false,
        ]);
        
        $event = new LoginAttempted($attempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('info')
            ->with('New location login detected', \Mockery::type('array'));
    });
    
    it('does not check new location when user is null', function () {
        $attempt = LoginAttempt::factory()->create([
            'user_id' => null,
            'successful' => true,
        ]);
        
        $event = new LoginAttempted($attempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('info')
            ->with('New location login detected', \Mockery::type('array'));
    });
    
    it('does not check new location when attempt has no user', function () {
        $attempt = LoginAttempt::factory()->create([
            'successful' => true,
        ]);
        
        // Remove user relationship
        $attempt->setRelation('user', null);
        
        $event = new LoginAttempted($attempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('info')
            ->with('New location login detected', \Mockery::type('array'));
    });
    
    it('respects configuration thresholds', function () {
        Config::set('classicauth.tracking.suspicious_activity.ip_failures_threshold', 5);
        
        // Create exactly at threshold
        LoginAttempt::factory()->count(5)->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
            'attempted_at' => now(),
        ]);
        
        $newAttempt = LoginAttempt::factory()->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldHaveReceived('channel')->with('security')->once();
    });
    
    it('respects time windows for suspicious activity', function () {
        // Create old failed attempts (outside window)
        LoginAttempt::factory()->count(10)->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
            'attempted_at' => now()->subHours(2), // Outside 1 hour window
        ]);
        
        $newAttempt = LoginAttempt::factory()->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
        ]);
        
        $event = new LoginAttempted($newAttempt);
        $listener = new LogLoginAttempt();
        
        $listener->handle($event);
        
        Log::shouldNotHaveReceived('channel')->with('security');
    });
});

describe('Private Methods', function () {
    it('correctly identifies suspicious activity', function () {
        $listener = new LogLoginAttempt();
        $reflection = new ReflectionClass($listener);
        $method = $reflection->getMethod('isSuspiciousActivity');
        $method->setAccessible(true);
        
        // Create scenario for suspicious activity
        LoginAttempt::factory()->count(10)->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
            'attempted_at' => now(),
        ]);
        
        $attempt = LoginAttempt::factory()->create([
            'ip_address' => '192.168.1.100',
            'successful' => false,
        ]);
        
        $result = $method->invoke($listener, $attempt);
        
        expect($result)->toBeTrue();
    });
    
    it('correctly identifies new location', function () {
        $user = User::factory()->create();
        $listener = new LogLoginAttempt();
        $reflection = new ReflectionClass($listener);
        $method = $reflection->getMethod('isNewLocation');
        $method->setAccessible(true);
        
        $attempt = LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'successful' => true,
        ]);
        
        $result = $method->invoke($listener, $attempt);
        
        expect($result)->toBeTrue();
    });
    
    it('returns false for new location when user is missing', function () {
        $listener = new LogLoginAttempt();
        $reflection = new ReflectionClass($listener);
        $method = $reflection->getMethod('isNewLocation');
        $method->setAccessible(true);
        
        $attempt = LoginAttempt::factory()->create([
            'user_id' => null,
            'successful' => true,
        ]);
        
        $result = $method->invoke($listener, $attempt);
        
        expect($result)->toBeFalse();
    });
    
    it('handleSuspiciousActivity logs warning', function () {
        $listener = new LogLoginAttempt();
        $reflection = new ReflectionClass($listener);
        $method = $reflection->getMethod('handleSuspiciousActivity');
        $method->setAccessible(true);
        
        $attempt = LoginAttempt::factory()->create([
            'email' => 'suspicious@example.com',
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Suspicious Bot',
        ]);
        
        $method->invoke($listener, $attempt);
        
        Log::shouldHaveReceived('channel')->with('security')->once();
        Log::shouldHaveReceived('warning')
            ->with('Suspicious login activity detected', \Mockery::type('array'))
            ->once();
    });
    
    it('notifyUserOfNewLocation logs info', function () {
        $listener = new LogLoginAttempt();
        $reflection = new ReflectionClass($listener);
        $method = $reflection->getMethod('notifyUserOfNewLocation');
        $method->setAccessible(true);
        
        $user = User::factory()->create();
        $attempt = LoginAttempt::factory()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => '192.168.1.100',
        ]);
        
        $method->invoke($listener, $attempt);
        
        Log::shouldHaveReceived('info')
            ->with('New location login detected', \Mockery::type('array'))
            ->once();
    });
});
