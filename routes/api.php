<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routes (clean, named)

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register/step-1', [AuthController::class, 'registerStepOne'])->name('register.step1');

    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login');
    Route::post('google/login', [AuthController::class, 'googleLogin'])->name('google.login');
    Route::post('refresh-token', [AuthController::class, 'refreshToken'])->middleware('throttle:refresh-token')->name('refresh-token');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password')->name('forgot-password');
    Route::post('verify-reset-otp', [AuthController::class, 'verifyResetOtp'])->middleware('throttle:verify-reset-otp')->name('verify-reset-otp');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::post('resend-reset-otp', [AuthController::class, 'resendResetOtp'])->middleware('throttle:resend-reset-otp')->name('resend-reset-otp');
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('register/step-2', [AuthController::class, 'registerStepTwo'])->name('register.step2');
        Route::post('register/step-3', [AuthController::class, 'registerStepThree'])->name('register.step3');
        Route::post('email/verification/send', [AuthController::class, 'sendEmailVerification'])->middleware('throttle:resend-verification')->name('verification.send');
        Route::post('email/verification/resend', [AuthController::class, 'resendEmailVerification'])->middleware('throttle:resend-verification')->name('verification.resend');
        Route::get('email/verification-status', [AuthController::class, 'verificationStatus'])->name('verification.status');

        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::middleware('verified.email')->group(function () {
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');
            Route::get('sessions', [AuthController::class, 'sessions'])->name('sessions.index');
            Route::delete('sessions/{sessionId}', [AuthController::class, 'revokeSession'])->name('sessions.revoke');
        });

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
    });
});
