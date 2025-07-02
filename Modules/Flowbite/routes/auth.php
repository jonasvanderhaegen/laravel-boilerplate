<?php

declare(strict_types=1);

Route::middleware(['guest', 'throttle:guest-auth'])->group(function () {
    Route::get('login', Modules\Flowbite\Livewire\Pages\Login::class)->name('login');
    Route::get('register', Modules\Flowbite\Livewire\Pages\Register::class)->name('register');
    Route::get('forgot-password', Modules\Flowbite\Livewire\Pages\PasswordRequest::class)->name('password.request');
    Route::get('reset-password/{token}', Modules\Flowbite\Livewire\Pages\PasswordReset::class)->name('password.reset');
});

Route::middleware(['auth'])->group(function () {
    Route::get('verify-email', Modules\Flowbite\Livewire\Pages\VerifyEmail::class)
        ->name('verification.notice')
        ->middleware('throttle:6,1');
    
    Route::get('verify-email/{id}/{hash}', Modules\Flowbite\Livewire\Pages\VerifyEmailConfirmation::class)
        ->name('verification.verify')
        ->middleware(['signed', 'throttle:6,1']);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Modules\Flowbite\Livewire\Pages\Dashboard::class)->name('dashboard');
});
