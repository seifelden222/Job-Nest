<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseEnrollmentController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ServiceConversationController;
use App\Http\Controllers\Api\ServiceProposalController;
use App\Http\Controllers\Api\ServiceRequestController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\TrainingProviderProfileController;
use App\Http\Controllers\Api\UserDocumentController;
use App\Http\Controllers\Api\UserInterestController;
use App\Http\Controllers\Api\UserLanguageController;
use App\Http\Controllers\Api\UserSkillsController;
use Illuminate\Support\Facades\Route;

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('courses/{course}/reviews', [CourseReviewController::class, 'index'])->name('courses.reviews.index');
Route::get('service-requests', [ServiceRequestController::class, 'index'])->name('service-requests.index');
Route::get('service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('service-requests.show');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('jobs', [JobController::class, 'store'])->name('jobs.store');
    Route::put('jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::delete('jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');

    Route::get('jobs/{job}/applications', [ApplicationController::class, 'index'])->name('jobs.applications.index');
    Route::post('jobs/{job}/applications', [ApplicationController::class, 'store'])->name('jobs.applications.store');
    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::put('applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
    Route::delete('applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');

    Route::get('conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::post('conversations', [ConversationController::class, 'store'])->name('conversations.store');
    Route::get('conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index'])->name('conversations.messages.index');
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store'])->name('conversations.messages.store');

    Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
    Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    Route::post('courses/{course}/enrollments', [CourseEnrollmentController::class, 'store'])->name('courses.enrollments.store');
    Route::get('courses/{course}/enrollments', [CourseEnrollmentController::class, 'providerIndex'])->name('courses.enrollments.index');
    Route::post('courses/{course}/reviews', [CourseReviewController::class, 'store'])->name('courses.reviews.store');
    Route::put('course-reviews/{courseReview}', [CourseReviewController::class, 'update'])->name('course-reviews.update');
    Route::delete('course-reviews/{courseReview}', [CourseReviewController::class, 'destroy'])->name('course-reviews.destroy');
    Route::get('course-enrollments', [CourseEnrollmentController::class, 'index'])->name('course-enrollments.index');
    Route::put('course-enrollments/{courseEnrollment}', [CourseEnrollmentController::class, 'update'])->name('course-enrollments.update');

    Route::post('service-requests', [ServiceRequestController::class, 'store'])->name('service-requests.store');
    Route::put('service-requests/{serviceRequest}', [ServiceRequestController::class, 'update'])->name('service-requests.update');
    Route::delete('service-requests/{serviceRequest}', [ServiceRequestController::class, 'destroy'])->name('service-requests.destroy');
    Route::get('service-requests/{serviceRequest}/proposals', [ServiceProposalController::class, 'index'])->name('service-requests.proposals.index');
    Route::post('service-requests/{serviceRequest}/proposals', [ServiceProposalController::class, 'store'])->name('service-requests.proposals.store');
    Route::get('service-proposals/{serviceProposal}', [ServiceProposalController::class, 'show'])->name('service-proposals.show');
    Route::put('service-proposals/{serviceProposal}', [ServiceProposalController::class, 'update'])->name('service-proposals.update');
    Route::post('service-proposals/{serviceProposal}/conversation', [ServiceConversationController::class, 'store'])->name('service-proposals.conversation.store');
});

// Auth routes (clean, named)

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register/step-1', [AuthController::class, 'registerStepOne'])->name('register.step1');
    Route::post('register/company', [AuthController::class, 'registerCompany'])->name('register.company');

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
        // User profile management routes
        Route::apiResource('user-skills', UserSkillsController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('user-interests', UserInterestController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('user-languages', UserLanguageController::class)->only(['index', 'store', 'destroy']);
        Route::apiResource('user-documents', UserDocumentController::class)->only(['index', 'store', 'destroy']);

        // Admin routes for managing skills, interests, and languages
        Route::apiResource('skills', SkillController::class);
        Route::apiResource('interests', InterestController::class);
        Route::apiResource('languages', LanguageController::class);
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

        // Protected routes that require email verification
        Route::middleware('verified.email')->group(function () {
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');
            Route::get('sessions', [AuthController::class, 'sessions'])->name('sessions.index');
            Route::delete('sessions/{sessionId}', [AuthController::class, 'revokeSession'])->name('sessions.revoke');
        });

        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('training-provider-profile', [TrainingProviderProfileController::class, 'show'])->name('training-provider-profile.show');
        Route::post('training-provider-profile', [TrainingProviderProfileController::class, 'update'])->name('training-provider-profile.store');
        Route::put('training-provider-profile', [TrainingProviderProfileController::class, 'update'])->name('training-provider-profile.update');
        Route::get('my-courses', [CourseController::class, 'myCourses'])->name('courses.my');
        Route::get('my-service-requests', [ServiceRequestController::class, 'myRequests'])->name('service-requests.my');

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
    });
});
