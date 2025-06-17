<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;

// name it whatever makes sense—you can even scope by directory
dataset('web pages', [
    'blog index' => [
        'routeName' => 'flowbite.blog.index',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Blog\Index::class,
        'limit' => 60,
    ],
    'events index' => [
        'routeName' => 'flowbite.events.index',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Events\Index::class,
        'limit' => 60,
    ],
    'home' => [
        'routeName' => 'flowbite.homepage',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Home::class,
        'limit' => 60,
    ],
    'home' => [
        'routeName' => 'flowbite.homepage',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Home::class,
        'limit' => 60,
    ],
    'company' => [
        'routeName' => 'flowbite.company',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Company::class,
        'limit' => 60,
    ],
    'contact' => [
        'routeName' => 'flowbite.contact',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Contact::class,
        'limit' => 5,
    ],
    'faq' => [
        'routeName' => 'flowbite.faq',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Faq::class,
        'limit' => 60,
    ],
    'features' => [
        'routeName' => 'flowbite.features',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Features::class,
        'limit' => 60,
    ],
    'market place' => [
        'routeName' => 'flowbite.marketplace',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Marketplace::class,
        'limit' => 60,
    ],
    'team' => [
        'routeName' => 'flowbite.team',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Team::class,
        'limit' => 60,
    ],
    'login' => [
        'routeName' => 'flowbite.login',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Login::class,
        'limit' => 10,
    ],
    'register' => [
        'routeName' => 'flowbite.register',
        'componentClass' => Modules\Flowbite\Livewire\Pages\Register::class,
        'limit' => 10,
    ],
]);

// 2) Shared setup (if needed)
beforeEach(function (): void {
    RateLimiter::clear('info-pages:'.$this->app['request']->ip());
});

it('renders the Livewire component', function (
    string $routeName,
    string $componentClass,
    int $limit
) {
    Livewire::test($componentClass)
        ->assertStatus(200);
})->with('web pages');

it('responds OK over HTTP', function (
    string $routeName,
    string $componentClass,
    int $limit
) {
    $this->get(route($routeName))
        ->assertOk();
})->with('web pages');

it('throttles after the configured limit', function (
    string $routeName,
    string $componentClass,
    int $limit
) {
    $url = route($routeName);

    for ($i = 1; $i <= $limit; ++$i) {
        $this->get($url)->assertStatus(200);
    }

    $this->get($url)->assertStatus(429);
})->with('web pages');
