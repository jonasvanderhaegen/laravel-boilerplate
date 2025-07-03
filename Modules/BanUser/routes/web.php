<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BanUser\Http\Controllers\BanUserController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('banusers', BanUserController::class)->names('banuser');
});
