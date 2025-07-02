<?php

declare(strict_types=1);

namespace Modules\ClassicAuth\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;

final class TestAuthEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:test-events 
                            {event : The event to test (login-success, login-fail, register, etc.)}
                            {--user= : User ID to use for testing}
                            {--email= : Email to use for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test authentication events by dispatching them manually';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $event = $this->argument('event');
        $userId = $this->option('user');
        $email = $this->option('email') ?? 'test@example.com';

        $this->info("Testing event: {$event}");
        $this->newLine();

        try {
            switch ($event) {
                case 'login-success':
                    $this->testLoginSuccess($userId);
                    break;
                case 'login-fail':
                    $this->testLoginFail($email);
                    break;
                case 'register':
                    $this->testRegister();
                    break;
                case 'password-reset':
                    $this->testPasswordReset($email);
                    break;
                case 'suspicious':
                    $this->testSuspiciousActivity($email);
                    break;
                case 'new-device':
                    $this->testNewDevice($userId);
                    break;
                default:
                    $this->error("Unknown event: {$event}");
                    $this->info('Available events: login-success, login-fail, register, password-reset, suspicious, new-device');

                    return 1;
            }

            $this->info('Event dispatched successfully!');
            $this->info('Check your logs and listeners for the results.');

        } catch (Exception $e) {
            $this->error('Error dispatching event: '.$e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Test login success event.
     */
    protected function testLoginSuccess(?string $userId): void
    {
        $user = $userId ? User::find($userId) : User::first();

        if (! $user) {
            $this->error('No user found. Please specify a valid user ID.');

            return;
        }

        event(new \Modules\ClassicAuth\Events\Login\LoginSucceeded(
            $user,
            '127.0.0.1',
            'Mozilla/5.0 (Test) Console Command',
            false
        ));

        $this->info("Dispatched LoginSucceeded event for user: {$user->email}");
    }

    /**
     * Test login fail event.
     */
    protected function testLoginFail(string $email): void
    {
        event(new \Modules\ClassicAuth\Events\Login\LoginFailed(
            $email,
            '127.0.0.1',
            'Mozilla/5.0 (Test) Console Command',
            'invalid_credentials'
        ));

        $this->info("Dispatched LoginFailed event for email: {$email}");
    }

    /**
     * Test register event.
     */
    protected function testRegister(): void
    {
        // Create a test user
        $user = User::factory()->make([
            'email' => 'newuser@test.com',
            'name' => 'Test User',
        ]);

        event(new \Modules\ClassicAuth\Events\Registration\UserRegistered(
            $user,
            '127.0.0.1',
            'Mozilla/5.0 (Test) Console Command',
            true
        ));

        $this->info("Dispatched UserRegistered event for: {$user->email}");
    }

    /**
     * Test password reset event.
     */
    protected function testPasswordReset(string $email): void
    {
        event(new \Modules\ClassicAuth\Events\PasswordReset\PasswordResetRequested(
            $email,
            '127.0.0.1',
            'Mozilla/5.0 (Test) Console Command'
        ));

        $this->info("Dispatched PasswordResetRequested event for: {$email}");
    }

    /**
     * Test suspicious activity event.
     */
    protected function testSuspiciousActivity(string $email): void
    {
        event(new \Modules\ClassicAuth\Events\Security\SuspiciousActivityDetected(
            'brute_force',
            '192.168.1.100',
            $email,
            [
                'failed_attempts' => 15,
                'time_window' => 300,
            ]
        ));

        $this->info('Dispatched SuspiciousActivityDetected event');
    }

    /**
     * Test new device event.
     */
    protected function testNewDevice(?string $userId): void
    {
        $user = $userId ? User::find($userId) : User::first();

        if (! $user) {
            $this->error('No user found. Please specify a valid user ID.');

            return;
        }

        event(new \Modules\ClassicAuth\Events\UserNotifications\NewDeviceLogin(
            $user,
            '192.168.1.100',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
            'San Francisco, CA',
            'iPhone 12'
        ));

        $this->info("Dispatched NewDeviceLogin event for user: {$user->email}");
    }
}
