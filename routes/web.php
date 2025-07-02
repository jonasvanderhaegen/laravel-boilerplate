<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware('throttle:info-pages')->group(function () {
    Route::get('/', Modules\Flowbite\Livewire\Pages\Home::class)->name('home');
});
