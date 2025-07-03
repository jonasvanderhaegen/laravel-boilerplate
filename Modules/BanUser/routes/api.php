<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\BanUser\Http\Controllers\BanUserController;

Route::prefix('v1/bans')->group(function () {
    // Public endpoint to report users (could be protected with different auth if needed)
    Route::post('/report', [BanUserController::class, 'reportUser'])->name('banuser.report');
    
    // Protected endpoints
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/check', [BanUserController::class, 'checkBan'])->name('banuser.check');
        Route::post('/lift', [BanUserController::class, 'liftBan'])->name('banuser.lift');
    });
});
