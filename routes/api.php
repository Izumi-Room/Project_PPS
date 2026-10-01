<?php

use App\Http\Controllers\Api\CurrentUserApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/me', [CurrentUserApiController::class, 'me'])->name('api.me');

    Route::middleware('role:SUPERADMIN')->group(function () {
        Route::get('/superadmin/check', [CurrentUserApiController::class, 'superadminCheck'])->name('api.superadmin.check');
    });
});
