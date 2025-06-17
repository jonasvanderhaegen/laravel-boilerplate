<?php

declare(strict_types=1);

use App\Models\User;

// name it whatever makes sense—you can even scope by directory
dataset('pages', [
    'discover' => [
        'routeName' => 'flowbite.verification.notice',
        'componentClass' => Modules\Flowbite\Livewire\Pages\VerifyEmail::class,
        'limit' => 6,
    ],
]);

// 2) Shared setup (if needed)
beforeEach(function (): void {
    $this->user = User::factory()->unverified()->create();
    $this->actingAs($this->user);
});

it('renders the Livewire component', function (
    string $routeName,
    string $componentClass,
    int $limit
) {
    Livewire::test($componentClass)
        ->assertStatus(200);
})->with('pages');

it('responds OK over HTTP', function (
    string $routeName,
    string $componentClass,
    int $limit
) {
    $this->get(route($routeName))
        ->assertOk();
})->with('pages');

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
})->with('pages');
