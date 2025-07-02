<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ClassicAuth\Actions\LogoutAction;

Route::post('logout', LogoutAction::class)
    ->middleware(['auth', 'throttle:guest-auth'])
    ->name('logout');
