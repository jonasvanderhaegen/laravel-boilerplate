<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Modules\ClassicAuth\Console\Commands\CleanupLoginAttempts;
use Modules\ClassicAuth\Models\LoginAttempt;

describe('CleanupLoginAttempts Command', function () {
    beforeEach(function () {
        Config::set('classicauth.tracking.retention_days', 30);
        LoginAttempt::query()->delete();
    });
    
    it('returns success when no attempts to clean up', function () {
        $this->artisan('classicauth:cleanup-login-attempts')
            ->expectsOutput('No login attempts to clean up.')
            ->assertSuccessful();
    });
    
    it('returns success when retention is disabled', function () {
        Config::set('classicauth.tracking.retention_days', null);
        
        $this->artisan('classicauth:cleanup-login-attempts')
            ->expectsOutput('Login attempt cleanup is disabled (retention_days is not set).')
            ->assertSuccessful();
    });
    
    it('deletes old login attempts', function () {
        // Create old attempts
        LoginAttempt::factory()->count(5)->create([
            'attempted_at' => now()->subDays(40),
        ]);
        
        // Create recent attempts
        LoginAttempt::factory()->count(3)->create([
            'attempted_at' => now()->subDays(10),
        ]);
        
        $this->artisan('classicauth:cleanup-login-attempts')
            ->expectsOutput('Deleting 5 login attempts older than 30 days...')
            ->expectsOutput('Successfully deleted 5 login attempt records.')
            ->assertSuccessful();
        
        expect(LoginAttempt::count())->toBe(3);
    });
    
    it('respects custom days option', function () {
        LoginAttempt::factory()->count(3)->create([
            'attempted_at' => now()->subDays(8),
        ]);
        
        LoginAttempt::factory()->count(2)->create([
            'attempted_at' => now()->subDays(5),
        ]);
        
        $this->artisan('classicauth:cleanup-login-attempts', ['--days' => 7])
            ->expectsOutput('Deleting 3 login attempts older than 7 days...')
            ->expectsOutput('Successfully deleted 3 login attempt records.')
            ->assertSuccessful();
        
        expect(LoginAttempt::count())->toBe(2);
    });
    
    // @codeCoverageIgnoreStart
    it('shows preview in dry run mode with successful attempt', function () {
        // Create both successful and failed attempts to ensure the closure handles both
        $successfulAttempt = LoginAttempt::factory()->create([
            'attempted_at' => now()->subDays(40),
            'email' => 'success@example.com',
            'ip_address' => '192.168.1.1',
            'successful' => true,
        ]);
        
        $failedAttempts = LoginAttempt::factory()->count(2)->create([
            'attempted_at' => now()->subDays(40),
            'email' => 'failed@example.com',
            'ip_address' => '192.168.1.2',
            'successful' => false,
        ]);
        
        $allAttempts = collect([$successfulAttempt])->merge($failedAttempts);
        
        $this->artisan('classicauth:cleanup-login-attempts', ['--dry-run' => true])
            ->expectsOutput('Would delete 3 login attempts older than 30 days.')
            ->expectsTable(
                ['ID', 'Email', 'IP', 'Status', 'Attempted At'],
                $allAttempts->map(function ($attempt) {
                    return [
                        $attempt->id,
                        $attempt->email,
                        $attempt->ip_address,
                        $attempt->successful ? 'Success' : 'Failed', // This ensures both branches are tested
                        $attempt->attempted_at->format('Y-m-d H:i:s'),
                    ];
                })->toArray()
            )
            ->assertSuccessful();
        
        // Verify nothing was deleted
        expect(LoginAttempt::count())->toBe(3);
    });
    // @codeCoverageIgnoreEnd
    
    it('shows sample and count for large dry runs', function () {
        // Create 10 old attempts with mixed success status
        LoginAttempt::factory()->count(5)->create([
            'attempted_at' => now()->subDays(40),
            'successful' => true,
        ]);
        
        LoginAttempt::factory()->count(5)->create([
            'attempted_at' => now()->subDays(40),
            'successful' => false,
        ]);
        
        $this->artisan('classicauth:cleanup-login-attempts', ['--dry-run' => true])
            ->expectsOutput('Would delete 10 login attempts older than 30 days.')
            ->expectsOutputToContain('... and 5 more records.')
            ->assertSuccessful();
    });
    
    it('uses cutoff date correctly', function () {
        // Create attempts at exact boundary
        LoginAttempt::factory()->create([
            'attempted_at' => now()->subDays(30)->addMinute(),
        ]);
        
        LoginAttempt::factory()->create([
            'attempted_at' => now()->subDays(30)->subMinute(),
        ]);
        
        $this->artisan('classicauth:cleanup-login-attempts')
            ->expectsOutput('Deleting 1 login attempts older than 30 days...')
            ->assertSuccessful();
        
        expect(LoginAttempt::count())->toBe(1);
    });
    
    it('handles zero retention days configuration', function () {
        Config::set('classicauth.tracking.retention_days', 0);
        
        $this->artisan('classicauth:cleanup-login-attempts')
            ->expectsOutput('Login attempt cleanup is disabled (retention_days is not set).')
            ->assertSuccessful();
    });
    
    it('prefers command option over config', function () {
        Config::set('classicauth.tracking.retention_days', 30);
        
        LoginAttempt::factory()->count(2)->create([
            'attempted_at' => now()->subDays(15),
        ]);
        
        $this->artisan('classicauth:cleanup-login-attempts', ['--days' => 10])
            ->expectsOutput('Deleting 2 login attempts older than 10 days...')
            ->assertSuccessful();
    });
});

describe('CleanupLoginAttempts Command Properties', function () {
    it('has correct signature', function () {
        $command = new CleanupLoginAttempts();
        
        expect($command->getName())->toBe('classicauth:cleanup-login-attempts');
    });
    
    it('has correct description', function () {
        $command = new CleanupLoginAttempts();
        $reflection = new ReflectionClass($command);
        $property = $reflection->getProperty('description');
        $property->setAccessible(true);
        
        expect($property->getValue($command))
            ->toBe('Clean up old login attempt records based on retention policy');
    });
});
