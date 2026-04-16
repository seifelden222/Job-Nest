<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routes (clean, named)

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register/step-1', [AuthController::class, 'registerStepOne'])->name('register.step1');

    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('register/step-2', [AuthController::class, 'registerStepTwo'])->name('register.step2');
        Route::post('register/step-3', [AuthController::class, 'registerStepThree'])->name('register.step3');

        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});
