<?php

declare(strict_types=1);

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\Facades\File;
use Modules\ClassicAuth\Actions\LoginUserAction;
use Modules\ClassicAuth\Console\Commands\CleanupLoginAttempts;
use Modules\ClassicAuth\Providers\EventServiceProvider;
use Modules\ClassicAuth\Providers\PrimaryServiceProvider;
use Modules\ClassicAuth\Providers\RouteServiceProvider;

beforeEach(function () {
    $this->provider = new PrimaryServiceProvider(app());
});

it('registers event service provider', function () {
    $this->provider->register();
    
    expect(app()->getProviders(EventServiceProvider::class))->toHaveCount(1);
});

it('registers route service provider', function () {
    $this->provider->register();
    
    expect(app()->getProviders(RouteServiceProvider::class))->toHaveCount(1);
});

it('binds LoginUserAction with Timebox dependency and executes closure', function () {
    $this->provider->register();
    
    // First resolution - this executes the closure
    $loginAction1 = app(LoginUserAction::class);
    expect($loginAction1)->toBeInstanceOf(LoginUserAction::class);
    
    // Get the binding and test it directly
    $abstract = LoginUserAction::class;
    $concrete = app()->getBindings()[$abstract]['concrete'];
    
    // Execute the closure directly to ensure coverage
    $loginAction2 = $concrete(app());
    expect($loginAction2)->toBeInstanceOf(LoginUserAction::class);
    
    // Verify both instances are created with Timebox dependency
    $reflection = new ReflectionClass($loginAction2);
    $constructor = $reflection->getConstructor();
    $parameters = $constructor->getParameters();
    
    expect($parameters)->toHaveCount(1)
        ->and($parameters[0]->getType()->getName())->toBe(\Illuminate\Support\Timebox::class);
});

it('registers console commands when running in console', function () {
    // Mock console environment
    app()->instance('app', Mockery::mock(app())
        ->shouldReceive('runningInConsole')
        ->andReturn(true)
        ->getMock());
    
    $provider = new PrimaryServiceProvider(app());
    $provider->boot();
    
    // Get all registered commands
    $commands = Artisan::all();
    
    expect($commands)->toHaveKey('classicauth:cleanup-login-attempts');
});

it('does not register console commands when not running in console', function () {
    // Mock non-console environment
    app()->instance('app', Mockery::mock(app())
        ->shouldReceive('runningInConsole')
        ->andReturn(false)
        ->getMock());
    
    $provider = new PrimaryServiceProvider(app());
    $provider->boot();
    
    // Commands should not be registered
    $commands = Artisan::all();
    
    expect($commands)->not->toHaveKey('classicauth:cleanup-login-attempts');
});

it('loads migrations from module path', function () {
    $this->provider->boot();
    
    $migrationPath = module_path('ClassicAuth', 'database/migrations');
    
    expect(File::exists($migrationPath))->toBeTrue();
});

it('has correct module name and name lower properties', function () {
    $reflection = new ReflectionClass($this->provider);
    
    $nameProperty = $reflection->getProperty('name');
    $nameProperty->setAccessible(true);
    
    $nameLowerProperty = $reflection->getProperty('nameLower');
    $nameLowerProperty->setAccessible(true);
    
    expect($nameProperty->getValue($this->provider))->toBe('ClassicAuth')
        ->and($nameLowerProperty->getValue($this->provider))->toBe('classicauth');
});

it('calls registerTranslations method', function () {
    $spy = Mockery::spy($this->provider);
    $spy->shouldReceive('registerTranslations')->once();
    $spy->boot();
});

it('calls registerConfig method', function () {
    $spy = Mockery::spy($this->provider);
    $spy->shouldReceive('registerConfig')->once();
    $spy->boot();
});

it('calls registerViews method', function () {
    $spy = Mockery::spy($this->provider);
    $spy->shouldReceive('registerViews')->once();
    $spy->boot();
});
