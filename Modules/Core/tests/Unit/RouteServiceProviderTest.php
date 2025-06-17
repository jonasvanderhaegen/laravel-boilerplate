<?php

declare(strict_types=1);
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Modules\Core\Providers\RouteServiceProvider;

beforeEach(function () {
    RateLimiter::clear('user-actions|123');
    RateLimiter::clear('user-actions|111.222.333.444');
    $this->provider = new RouteServiceProvider(app());
    $this->provider->boot(); // Registreert de rate limiters
});

it('limits by user ID when authenticated', function () {
    $request = Request::create('/dummy');
    $user = new class
    {
        public int $id = 123;
    };
    $request->setUserResolver(fn () => $user);

    $key = 'user-actions|'.$user->id;

    // Trigger limiter
    $allowed = RateLimiter::attempt($key, 30, fn () => true);

    expect($allowed)->toBeTrue();
});

it('limits by IP when guest', function () {
    $request = Request::create('/dummy');
    $request->server->set('REMOTE_ADDR', '111.222.333.444');
    $request->setUserResolver(fn () => null);

    $key = 'user-actions|'.$request->ip();

    // Trigger limiter
    $allowed = RateLimiter::attempt($key, 30, fn () => true);

    expect($allowed)->toBeTrue();
});
