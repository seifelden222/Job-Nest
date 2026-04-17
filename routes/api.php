<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routes (clean, named)

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register/step-1', [AuthController::class, 'registerStepOne'])->name('register.step1');

    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('google/login', [AuthController::class, 'googleLogin'])->name('google.login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('verify-reset-otp', [AuthController::class, 'verifyResetOtp'])->name('verify-reset-otp');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::post('resend-reset-otp', [AuthController::class, 'resendResetOtp'])->name('resend-reset-otp');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('register/step-2', [AuthController::class, 'registerStepTwo'])->name('register.step2');
        Route::post('register/step-3', [AuthController::class, 'registerStepThree'])->name('register.step3');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');

        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::get('sessions', [AuthController::class, 'sessions'])->name('sessions.index');
        Route::delete('sessions/{sessionId}', [AuthController::class, 'revokeSession'])->name('sessions.revoke');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
    });
});
