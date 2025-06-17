<?php

declare(strict_types=1);

namespace Modules\Core\Tests\Unit\Stubs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Modules\Core\Concerns\WithRateLimiting;
use Modules\Core\Exceptions\TooManyRequestsException;
use Tests\TestCase;

/**
 * Create a simple “stub” class that uses the trait, so we can call its methods directly.
 */
final class RateLimiterStub
{
    use WithRateLimiting;

    // Expose protected/private methods for testing via public wrappers:

    public function publicGetRateLimitKey(string $method, ?string $component = null): string
    {
        return $this->getRateLimitKey($method, $component);
    }

    public function publicHitRateLimiter(?string $method = null, int $decaySeconds = 60, ?string $component = null): void
    {
        $this->hitRateLimiter($method, $decaySeconds, $component);
    }

    public function publicSecondsUntilReset(?string $method = null, ?string $component = null): int
    {
        return $this->secondsUntilReset($method, $component);
    }

    public function publicClearRateLimiter(?string $method = null, ?string $component = null): void
    {
        $this->clearRateLimiter($method, $component);
    }

    public function publicRateLimit(
        int $maxAttempts,
        int $decaySeconds = 60,
        ?string $method = null,
        ?string $component = null
    ): void {
        $this->rateLimit($maxAttempts, $decaySeconds, $method, $component);
    }

    public function publicRateLimitByEmail(int $maxAttempts, int $decaySeconds, string $email, string $auth): void
    {
        $this->rateLimitByEmail($maxAttempts, $decaySeconds, $email, $auth);
    }

    public function publicInitRateLimitCountdown(
        ?string $method = null,
        ?string $component = null,
        string|bool $auth = false
    ): void {
        $this->initRateLimitCountdown($method, $component, $auth);
    }
}

final class WithRateLimitingTest extends TestCase
{
    private RateLimiterStub $stub;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure RateLimiter is cleared before each test
        RateLimiter::clearResolvedInstances();

        $this->stub = new RateLimiterStub();

        // Ensure session/view starts fresh
        Session::flush();
        View::share('isMobile', null);
    }

    public function test_get_rate_limit_key_is_consistent_and_unique(): void
    {
        // Use a fake IP:
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '192.0.2.1']);

        $method = 'someMethod';
        $component = 'SomeComponent';

        $key1 = $this->stub->publicGetRateLimitKey($method, $component);
        $key2 = $this->stub->publicGetRateLimitKey($method, $component);

        // Both calls should produce the same key
        $this->assertSame($key1, $key2);

        // It should match the expected SHA1 format:
        $expected = 'livewire-rate-limiter:'.sha1($component.'|'.$method.'|'.request()->ip());
        $this->assertSame($expected, $key1);

        // Different method or component yields a different key
        $otherKey = $this->stub->publicGetRateLimitKey('otherMethod', $component);
        $this->assertNotSame($key1, $otherKey);
    }

    public function test_hit_rate_limiter_increases_seconds_until_reset_and_clear_resets_it(): void
    {
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '203.0.113.5']);

        $method = 'someAction';
        $component = 'SomeComponent';

        // Initially, no hits => availableIn should be 0
        $this->assertSame(0, $this->stub->publicSecondsUntilReset($method, $component));

        // Hit once with a decay of 60 seconds
        $this->stub->publicHitRateLimiter($method, 60, $component);

        // Now secondsUntilReset should be > 0
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

        // First call: under the limit, no exception
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Second call: under the limit, no exception
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Third call: exceeds the limit and should throw TooManyRequestsException
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

        // First call is fine
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Second call should throw
        $this->expectException(TooManyRequestsException::class);
        $this->stub->publicRateLimit($max, $decay, $method, $component);

        // Clear the limiter and try again
        $this->stub->publicClearRateLimiter($method, $component);

        // Now it should not throw
        try {
            $this->stub->publicRateLimit($max, $decay, $method, $component);
            $this->assertTrue(true, 'Did not throw after clearing');
        } catch (TooManyRequestsException) {
            $this->fail('Expected no exception after clearing rate limiter');
        }
    }

    public function test_rate_limit_by_email_tracks_attempts_and_throws_exception_properly(): void
    {
        // Simulate non-local environment
        $this->app['config']->set('app.env', 'production');

        $email = 'user@example.test';
        $auth = 'testAuth';
        $max = 2;
        $decay = 15;

        // First hit is fine
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);

        // Second hit is fine
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);

        // Third hit should trigger TooManyRequestsException:
        $this->expectException(TooManyRequestsException::class);
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);
    }

    public function test_rate_limit_by_email_does_not_throw_in_local_environment(): void
    {
        // Force “local” environment
        $this->app['config']->set('app.env', 'local');

        $email = 'dev@example.test';
        $auth = 'devAuth';
        $max = 1;
        $decay = 1;

        // Even though max=1, in “local” we return early—no exception
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);
        $this->stub->publicRateLimitByEmail($max, $decay, $email, $auth);

        // No exception means pass
        $this->assertTrue(true);
    }

    public function test_init_rate_limit_countdown_uses_session_value_if_present(): void
    {
        $email = 'session@example.test';
        $auth = 'login';

        // Manually store the session key that rateLimitByEmail uses
        Session::put("{$auth}_email", $email);

        // Next, manually set RateLimiter’s availableIn for that key to 45
        $key = "{$auth}_email:$email";
        RateLimiter::hit($key, 60);
        // Fast-forward time by 15 seconds:
        $this->travel(15)->seconds();
        // Now availableIn should be ~45
        $this->assertGreaterThanOrEqual(44, RateLimiter::availableIn($key));

        // Now call initRateLimitCountdown(): it should pick up from RateLimiter via session
        $this->stub->publicInitRateLimitCountdown(null, null, $auth);

        // Our public property $secondsUntilReset should match RateLimiter::availableIn($key)
        $this->assertSame(RateLimiter::availableIn($key), $this->stub->secondsUntilReset);
    }

    public function test_init_rate_limit_countdown_uses_seconds_until_reset_if_no_session(): void
    {
        Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '198.51.100.9']);

        $method = 'specialMethod';
        $component = 'SpecialComponent';

        // Ensure no session key for “auth”
        Session::forget('auth_email');

        // Hit the rate limiter so secondsUntilReset > 0
        $this->stub->publicHitRateLimiter($method, 60, $component);
        $expected = $this->stub->publicSecondsUntilReset($method, $component);

        // Call initRateLimitCountdown() without specifying auth => it uses secondsUntilReset
        $this->stub->publicInitRateLimitCountdown($method, $component, false);

        // secondsUntilReset property on the stub should now equal $expected
        $this->assertSame($expected, $this->stub->secondsUntilReset);
    }
}
