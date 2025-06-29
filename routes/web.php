<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', Modules\Flowbite\Livewire\Pages\Home::class)->name('home');
