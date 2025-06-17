<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Modules\Core\Exceptions\TooManyRequestsException;
use Modules\Core\Tests\Unit\Stubs\RateLimiterStub;
use Tests\TestCase;

final class WithRateLimitingTest extends TestCase
{
    private RateLimiterStub $stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure RateLimiter is cleared before each test
        RateLimiter::clearResolvedInstances();

        $this->stub = new RateLimiterStub();

        // Clear session and any shared view data
        Session::flush();
        View::share('isMobile', null);
    }

    public function test_get_rate_limit_key_is_consistent_and_unique(): void
    {
        // Fake a client IP
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.0.2.1']);

        $method = 'someMethod';
        $component = 'SomeComponent';

        $key1 = $this->stub->publicGetRateLimitKey($method, $component);
        $key2 = $this->stub->publicGetRateLimitKey($method, $component);

        // Both should be identical
        $this->assertSame($key1, $key2);

        $expected = 'livewire-rate-limiter:'.sha1($component.'|'.$method.'|'.request()->ip());
        $this->assertSame($expected, $key1);

        // A different method yields a different key
        $otherKey = $this->stub->publicGetRateLimitKey('otherMethod', $component);
        $this->assertNotSame($key1, $otherKey);
    }

    public function test_hit_rate_limiter_increases_seconds_until_reset_and_clearing_resets_it(): void
    {
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.5']);

        $method = 'someAction';
        $component = 'SomeComponent';

        // Initially zero
        $this->assertSame(0, $this->stub->publicSecondsUntilReset($method, $component));

        // Hit once
        $this->stub->publicHitRateLimiter($method, 60, $component);

        $seconds = $this->stub->publicSecondsUntilReset($method, $component);
        $this->assertIsInt($seconds);
        $this->assertGreaterThan(0, $seconds);

        // Clearing resets it back to zero
        $this->stub->publicClearRateLimiter($method, $component);
        $this->assertSame(0, $this->stub->publicSecondsUntilReset($method, $component));
    }

    public function test_rate_limit_throws_exception_after_exceeding_max_attempts(): void
    {
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.7']);

        $method = 'loginAttempt';
        $component = 'AuthComponent';
        $max = 2;
        $decay = 30;

        // Two attempts fine
        $this->stub->publicRateLimit($max, $decay, $method, $component);
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Third should throw
        $this->expectException(TooManyRequestsException::class);
        $this->stub->publicRateLimit($max, $decay, $method, $component);
    }

    public function test_clear_rate_limiter_allows_rate_limit_again_after_clearing(): void
    {
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.8']);

        $method = 'submitForm';
        $component = 'FormComponent';
        $max = 1;
        $decay = 10;

        // First fine
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Second should throw
        $this->expectException(TooManyRequestsException::class);
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Clear and try again—no exception
        $this->stub->publicClearRateLimiter($method, $component);
        try {
            $this->stub->publicRateLimit($max, $decay, $method, $component);
            $this->assertTrue(true);
        } catch (TooManyRequestsException) {
            $this->fail('Expected no exception after clearing');
        }
    }

    public function test_rate_limit_by_email_tracks_attempts_and_throws_exception_properly(): void
    {
        // Tell the application environment is “production”
        $this->app['env'] = 'production';

        $email = 'user@example.test';
        $auth = 'testAuth';
        $max = 2;
        $decay = 15;

        // Two hits fine
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);

        // Third should throw
        $this->expectException(TooManyRequestsException::class);
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);
    }

    public function test_init_rate_limit_countdown_uses_session_value_if_present(): void
    {
        $email = 'session@example.test';
        $auth = 'login';

        // Store the session key that rateLimitByEmail would set
        Session::put("{$auth}_email", $email);

        // Prime RateLimiter so availableIn is > 0
        $key = "{$auth}_email:$email";
        RateLimiter::hit($key, 60);
        $this->travel(15)->seconds();

        // Now initRateLimitCountdown should read from RateLimiter via session
        $this->stub->publicInitRateLimitCountdown(null, null, $auth);

        // The public $secondsUntilReset property should match RateLimiter::availableIn($key)
        $this->assertSame(RateLimiter::availableIn($key), $this->stub->secondsUntilReset);
    }

    public function test_init_rate_limit_countdown_uses_seconds_until_reset_if_no_session(): void
    {
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.9']);

        $method = 'specialMethod';
        $component = 'SpecialComponent';

        // Ensure no “auth_email” in session
        Session::forget('auth_email');

        // Hit the rate limiter so secondsUntilReset > 0
        $this->stub->publicHitRateLimiter($method, 60, $component);
        $expected = $this->stub->publicSecondsUntilReset($method, $component);

        // Call initRateLimitCountdown() with no session key—uses secondsUntilReset
        $this->stub->publicInitRateLimitCountdown($method, $component, false);

        // The public property should now equal $expected
        $this->assertSame($expected, $this->stub->secondsUntilReset);
    }
}
