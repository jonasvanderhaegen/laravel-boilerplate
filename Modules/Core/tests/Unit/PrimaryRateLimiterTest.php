<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    // Reset all existing limiters
    RateLimiter::clear('user-actions');
});

it('applies user-actions limit for guest using IP', function () {
    $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

    $limiter = RateLimiter::limiter('user-actions');
    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe(30)
        ->and($limit->decaySeconds)->toBe(60) // ✅ decay in seconden
        ->and($limit->key)->toBe('127.0.0.1');
});

it('applies user-actions limit for authenticated user using ID', function () {
    $user = new class
    {
        public int $id = 42;
    };

    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);

    $limit = RateLimiter::limiter('user-actions')($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->key)->toBe(42);
});

it('throttles after too many requests', function () {
    $ip = '123.45.67.89';
    $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

    // Simulate 30 hits
    for ($i = 0; $i < 30; ++$i) {
        RateLimiter::hit('user-actions|'.$ip);
    }

    // 31e request = blocked
    expect(RateLimiter::tooManyAttempts('user-actions|'.$ip, 30))->toBeTrue();
});
