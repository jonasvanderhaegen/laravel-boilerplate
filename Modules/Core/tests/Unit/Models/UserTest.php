<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Hash;

it('can be created via factory', function () {
    $user = User::factory()->create([
        'password' => 'secret123',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->not()->toBeEmpty()
        ->and($user->email)->toContain('@')
        ->and(Hash::check('secret123', $user->password))->toBeTrue();
});

it('has hidden attributes when converted to array', function () {
    $user = User::factory()->make();

    $array = $user->toArray();

    expect($array)->not()->toHaveKey('password')
        ->and($array)->not()->toHaveKey('remember_token');
});

it('casts email_verified_at to datetime', function () {
    $user = User::factory()->make([
        'email_verified_at' => '2024-01-01 12:34:56',
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(CarbonInterface::class);
});
