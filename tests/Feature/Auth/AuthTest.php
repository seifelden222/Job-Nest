<?php

use App\Mail\Auth\SendOtp;
use App\Models\Interest;
use App\Models\Language;
use App\Models\OtpCode;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ─── Helpers ────────────────────────────────────────────────────────────────

function personPayload(array $overrides = []): array
{
    return array_merge([
        'account_type' => 'person',
        'name' => 'Ali Hassan',
        'email' => 'ali@example.com',
        'phone' => '01012345678',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'university' => 'Cairo University',
        'major' => 'Computer Science',
    ], $overrides);
}

function companyPayload(array $overrides = []): array
{
    return array_merge([
        'account_type' => 'company',
        'name' => 'Tech Corp',
        'email' => 'hr@techcorp.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'company_name' => 'Tech Corp Ltd',
        'website' => 'https://techcorp.com',
        'company_size' => '51-200',
        'industry' => 'Technology',
        'location' => 'Cairo',
    ], $overrides);
}

// ─── Register Step 1 ────────────────────────────────────────────────────────

test('person can register step 1', function () {
    $this->postJson(route('auth.register.step1'), personPayload())
        ->assertCreated()
        ->assertJsonStructure(['message', 'token', 'user' => ['id', 'email', 'account_type', 'person_profile']]);

    $this->assertDatabaseHas('users', ['email' => 'ali@example.com', 'account_type' => 'person']);
    $this->assertDatabaseHas('person_profiles', ['university' => 'Cairo University']);
});

test('company can register step 1', function () {
    $this->postJson(route('auth.register.step1'), companyPayload())
        ->assertCreated()
        ->assertJsonStructure(['message', 'token', 'user' => ['id', 'email', 'account_type', 'company_profile']]);

    $this->assertDatabaseHas('users', ['email' => 'hr@techcorp.com', 'account_type' => 'company']);
    $this->assertDatabaseHas('company_profiles', ['company_name' => 'Tech Corp Ltd']);
});

test('register step 1 fails with duplicate email', function () {
    User::factory()->create(['email' => 'ali@example.com']);

    $this->postJson(route('auth.register.step1'), personPayload())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

// ─── Register Step 2 ────────────────────────────────────────────────────────

test('person can complete register step 2 with skills and languages', function () {
    $skill = Skill::create(['name' => 'PHP']);
    $language = Language::create(['name' => 'English']);

    $user = User::factory()->person()->create();
    $user->personProfile()->create(['user_id' => $user->id, 'onboarding_step' => 1]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson(route('auth.register.step2'), [
            'employment_status' => 'employed',
            'employment_type' => 'full_time',
            'current_job_title' => 'Developer',
            'preferred_work_location' => 'remote',
            'expected_salary_min' => 3000,
            'expected_salary_max' => 7000,
            'skills' => [$skill->id],
            'languages' => [$language->id],
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('person_profiles', ['user_id' => $user->id, 'onboarding_step' => 2]);
    $this->assertDatabaseHas('user_skills', ['user_id' => $user->id, 'skill_id' => $skill->id]);
    $this->assertDatabaseHas('user_languages', ['user_id' => $user->id, 'language_id' => $language->id]);
});

// ─── Register Step 3 ────────────────────────────────────────────────────────

test('person can complete register step 3 and upload files', function () {
    Storage::fake('public');

    $interest = Interest::create(['name' => 'AI']);

    $user = User::factory()->person()->create();
    $user->personProfile()->create(['user_id' => $user->id, 'onboarding_step' => 2]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson(route('auth.register.step3'), [
            'about' => 'Backend developer with 3 years of experience.',
            'interests' => [$interest->id],
            'profile_photo' => UploadedFile::fake()->image('photo.jpg'),
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
            'certificates' => [UploadedFile::fake()->create('cert.pdf', 100, 'application/pdf')],
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('person_profiles', ['user_id' => $user->id, 'is_profile_completed' => true]);
    $this->assertDatabaseHas('documents', ['user_id' => $user->id, 'type' => 'cv', 'is_primary' => true]);
    $this->assertDatabaseHas('documents', ['user_id' => $user->id, 'type' => 'certificate']);
    $this->assertDatabaseHas('user_interests', ['user_id' => $user->id, 'interest_id' => $interest->id]);
});

// ─── Login ───────────────────────────────────────────────────────────────────

test('user can login with correct credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('password123')]);

    $this->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => 'password123',
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['message', 'token', 'user']);
});

test('login fails with wrong password', function () {
    $user = User::factory()->create(['password' => Hash::make('correct')]);

    $this->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

// ─── Me & Logout ────────────────────────────────────────────────────────────

test('authenticated user can fetch their profile', function () {
    $user = User::factory()->person()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->getJson(route('auth.me'))
        ->assertSuccessful()
        ->assertJsonPath('user.id', $user->id);
});

test('user can logout and token is deleted', function () {
    $user = User::factory()->create();

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson(route('auth.logout'))
        ->assertSuccessful();

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

// ─── Forgot Password ────────────────────────────────────────────────────────

test('forgot password sends OTP by email', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);

    $this->postJson(route('auth.forgot-password'), [
        'method' => 'email',
        'email_or_phone' => 'user@example.com',
    ])
        ->assertSuccessful();

    Mail::assertSent(SendOtp::class);
    $this->assertDatabaseHas('otp_codes', ['user_id' => $user->id, 'email' => 'user@example.com']);
});

// ─── Verify OTP ─────────────────────────────────────────────────────────────

test('valid OTP is verified and verified_at is set', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);

    OtpCode::create([
        'user_type' => 'user',
        'user_id' => $user->id,
        'email' => 'user@example.com',
        'code' => '123456',
        'type' => 'reset_password',
        'expires_at' => now()->addMinutes(10),
        'verified_at' => null,
    ]);

    $this->postJson(route('auth.verify-reset-otp'), [
        'email_or_phone' => 'user@example.com',
        'otp' => '123456',
    ])
        ->assertSuccessful();

    expect(
        OtpCode::where('user_id', $user->id)->whereNotNull('verified_at')->exists()
    )->toBeTrue();
});

test('wrong OTP returns validation error', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);

    OtpCode::create([
        'user_type' => 'user',
        'user_id' => $user->id,
        'email' => 'user@example.com',
        'code' => '999999',
        'type' => 'reset_password',
        'expires_at' => now()->addMinutes(10),
        'verified_at' => null,
    ]);

    $this->postJson(route('auth.verify-reset-otp'), [
        'email_or_phone' => 'user@example.com',
        'otp' => '000000',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['otp']);
});

// ─── Resend OTP ─────────────────────────────────────────────────────────────

test('resend OTP invalidates old OTP and creates a new one', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'user@example.com']);

    OtpCode::create([
        'user_type' => 'user',
        'user_id' => $user->id,
        'email' => 'user@example.com',
        'code' => '111111',
        'type' => 'reset_password',
        'expires_at' => now()->addMinutes(10),
        'verified_at' => null,
    ]);

    $this->postJson(route('auth.resend-reset-otp'), [
        'method' => 'email',
        'email_or_phone' => 'user@example.com',
    ])
        ->assertSuccessful();

    $this->assertDatabaseMissing('otp_codes', ['code' => '111111']);
    $this->assertDatabaseCount('otp_codes', 1);

    Mail::assertSent(SendOtp::class);
});

// ─── Reset Password ──────────────────────────────────────────────────────────

test('password is reset with a valid verified OTP', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('oldpassword'),
    ]);

    OtpCode::create([
        'user_type' => 'user',
        'user_id' => $user->id,
        'email' => 'user@example.com',
        'code' => '654321',
        'type' => 'reset_password',
        'expires_at' => now()->addMinutes(10),
        'verified_at' => now(),
    ]);

    $this->postJson(route('auth.reset-password'), [
        'email_or_phone' => 'user@example.com',
        'otp' => '654321',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])
        ->assertSuccessful();

    expect(Hash::check('newpassword1', $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseCount('otp_codes', 0);
});

test('reset password fails with unverified OTP', function () {
    $user = User::factory()->create(['email' => 'user@example.com']);

    OtpCode::create([
        'user_type' => 'user',
        'user_id' => $user->id,
        'email' => 'user@example.com',
        'code' => '654321',
        'type' => 'reset_password',
        'expires_at' => now()->addMinutes(10),
        'verified_at' => null,
    ]);

    $this->postJson(route('auth.reset-password'), [
        'email_or_phone' => 'user@example.com',
        'otp' => '654321',
        'password' => 'newpassword1',
        'password_confirmation' => 'newpassword1',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['otp']);
});

// ─── Change Password ─────────────────────────────────────────────────────────

test('authenticated user can change their password', function () {
    $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson(route('auth.change-password'), [
            'old_password' => 'oldpassword',
            'password' => 'newpassword1',
            'password_confirmation' => 'newpassword1',
        ])
        ->assertSuccessful();

    expect(Hash::check('newpassword1', $user->fresh()->password))->toBeTrue();
});
