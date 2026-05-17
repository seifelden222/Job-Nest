<?php

use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\CourseReviews\CourseReviewResource;
use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Resources\OtpCodes\OtpCodeResource;
use App\Filament\Resources\RefreshTokens\RefreshTokenResource;
use App\Filament\Resources\SavedItems\SavedItemResource;
use App\Models\Admin;
use App\Models\Interest;
use App\Models\User;

test('filament admin panel uses the admin guard instead of the default web guard', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'web')
        ->get(route('filament.admin.pages.dashboard'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

test('active admins can access filament resources without policy signature crashes', function () {
    $admin = Admin::query()->create([
        'name' => 'Dashboard Admin',
        'email' => 'admin@example.com',
        'phone' => '01000000000',
        'password' => 'password',
        'status' => 'active',
    ]);

    Interest::query()->create([
        'name' => 'Product Design',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('filament.admin.resources.users.index'))
        ->assertSuccessful();

    $this->actingAs($admin, 'admin')
        ->get(route('filament.admin.resources.interests.index'))
        ->assertSuccessful()
        ->assertSee('Product Design');
});

test('active admins have super admin abilities across filament resources', function () {
    $admin = Admin::query()->create([
        'name' => 'Super Admin',
        'email' => 'super-admin@example.com',
        'phone' => '01000000004',
        'password' => 'password',
        'status' => 'active',
    ]);

    $this->actingAs($admin, 'admin');

    expect(CourseReviewResource::canCreate())->toBeTrue();
    expect(ConversationResource::canCreate())->toBeTrue();
    expect(MessageResource::canCreate())->toBeTrue();
    expect(SavedItemResource::canCreate())->toBeTrue();

    $this->get(route('filament.admin.resources.otp-codes.index'))
        ->assertSuccessful();

    $this->get(route('filament.admin.resources.refresh-tokens.index'))
        ->assertSuccessful();

    expect(OtpCodeResource::canDeleteAny())->toBeTrue();
    expect(RefreshTokenResource::canDeleteAny())->toBeTrue();
});
