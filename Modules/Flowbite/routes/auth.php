<?php

declare(strict_types=1);

Route::middleware(['guest', 'throttle:guest-auth'])->group(function () {
    Route::get('login', Modules\Flowbite\Livewire\Pages\Login::class)->name('login');
    Route::get('register', Modules\Flowbite\Livewire\Pages\Register::class)->name('register');
});

Route::middleware(['auth'])->group(function () {
    Route::get('verify-email', Modules\Flowbite\Livewire\Pages\VerifyEmail::class)
        ->name('verification.notice')
        ->middleware('throttle:6,1');
});

Route::middleware(['auth', 'verified'])->group(function () {});

Route::get('dashboard', Modules\Flowbite\Livewire\Pages\Dashboard::class)->name('dashboard');
