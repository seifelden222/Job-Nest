<?php

namespace App\Services\Auth;

use App\Http\Requests\Api\Auth\RegisterStepThreeRequest;
use App\Mail\Auth\RegisterComplete;
use App\Mail\Auth\RegisterStep1;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\PersonProfile;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        private AuthTokenService $authTokenService,
    ) {}

    public function registerStepOne(array $validated, Request $request): array
    {
        if (($validated['account_type'] ?? null) !== 'person') {
            throw ValidationException::withMessages([
                'account_type' => ['Company accounts must use /api/auth/register/company.'],
            ]);
        }

        try {

            return DB::transaction(function () use ($validated, $request): array {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'account_type' => $validated['account_type'],
                    'status' => 'active',
                ]);

                $this->createProfileForUser($user, $validated);
                $this->sendEmailVerification($user);
                $tokenData = $this->authTokenService->issueToken(
                    $user,
                    $request,
                    'register-step-1',
                    $validated['device_name'] ?? null,
                );
                $this->sendRegisterStep1Email($user);
                Log::info('Queued RegisterStep1', ['user_id' => $user->id, 'email' => $user->email]);

                return [
                    'user' => $this->loadUserProfiles($user),
                    ...$tokenData,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error in registerStepOne', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function registerCompany(array $validated, Request $request): array
    {
        if (($validated['account_type'] ?? null) !== 'company') {
            throw ValidationException::withMessages([
                'account_type' => ['Company registration requires account_type to be company.'],
            ]);
        }

        try {

            return DB::transaction(function () use ($validated, $request): array {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'account_type' => 'company',
                    'status' => 'active',
                ]);

                $this->createCompanyProfileForUser($user, $validated, $request);
                $this->sendEmailVerification($user);
                $tokenData = $this->authTokenService->issueToken(
                    $user,
                    $request,
                    'register-company',
                    $validated['device_name'] ?? null,
                );
                $this->sendRegisterCompleteEmail($user);
                Log::info('Queued RegisterComplete', ['user_id' => $user->id, 'email' => $user->email]);

                return [
                    'user' => $this->loadUserProfiles($user),
                    ...$tokenData,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error in registerCompany', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function registerStepTwo(User $user, array $validated): User
    {
        if ($user->isCompany()) {
            throw ValidationException::withMessages([
                'account_type' => ['Company accounts must use /api/auth/register/company.'],
            ]);
        }

        return DB::transaction(function () use ($user, $validated): User {
            if ($user->isPerson()) {
                $this->updatePersonStepTwo($user, $validated);
            }

            return $this->loadUserProfiles($user->fresh());
        });
    }

    public function registerStepThree(RegisterStepThreeRequest $request, User $user, array $validated): User
    {
        if ($user->isCompany()) {
            throw ValidationException::withMessages([
                'account_type' => ['Company accounts must use /api/auth/register/company.'],
            ]);
        }

        try {

            return DB::transaction(function () use ($request, $user, $validated): User {
                if ($user->isPerson()) {
                    $this->updatePersonStepThree($request, $user, $validated);
                }
                $this->sendRegisterCompleteEmail($user);

                Log::info('Queued RegisterComplete', ['user_id' => $user->id, 'email' => $user->email]);

                return $this->loadUserProfiles($user->fresh());
            });
        } catch (\Exception $e) {
            Log::error('Error in registerStepThree', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function login(array $validated, Request $request): array
    {
        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $tokenData = $this->authTokenService->issueToken(
            $user,
            $request,
            'login',
            $validated['device_name'] ?? null,
        );

        return [
            'user' => $this->loadUserProfiles($user),
            ...$tokenData,
        ];
    }

    public function me(User $user): User
    {
        return $this->loadUserProfiles($user);
    }

    public function logout(User $user): void
    {
        $accessToken = $user->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            $this->authTokenService->revokeRefreshTokensByAccessTokenId((int) $accessToken->getKey());
            $accessToken->delete();
        }
    }

    public function logoutAll(User $user): array
    {
        $revokedAccessTokensCount = $this->authTokenService->revokeAllTokens($user);
        $revokedRefreshTokensCount = $this->authTokenService->revokeAllRefreshTokens($user);

        return [
            'revoked_access_tokens_count' => $revokedAccessTokensCount,
            'revoked_refresh_tokens_count' => $revokedRefreshTokensCount,
        ];
    }

    public function createProfileForUser(User $user, array $validated = []): void
    {
        PersonProfile::create([
            'user_id' => $user->id,
            'university' => $validated['university'] ?? null,
            'major' => $validated['major'] ?? null,
            'onboarding_step' => 1,
            'is_profile_completed' => false,
        ]);
    }

    private function createCompanyProfileForUser(User $user, array $validated, Request $request): void
    {
        $attributes = [
            'user_id' => $user->id,
            'company_name' => $validated['company_name'],
            'website' => $validated['website'] ?? null,
            'company_size' => $validated['company_size'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'location' => $validated['location'] ?? null,
            'about' => $validated['about'] ?? null,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ];

        if ($request->hasFile('logo')) {
            $attributes['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        CompanyProfile::create($attributes);
    }

    private function updatePersonStepTwo(User $user, array $validated): void
    {
        $profile = $user->personProfile;

        $profile->update([
            'employment_status' => $validated['employment_status'] ?? $profile->employment_status,
            'employment_type' => $validated['employment_type'] ?? $profile->employment_type,
            'current_job_title' => $validated['current_job_title'] ?? $profile->current_job_title,
            'company_name' => $validated['company_name'] ?? $profile->company_name,
            'preferred_work_location' => $validated['preferred_work_location'] ?? $profile->preferred_work_location,
            'expected_salary_min' => $validated['expected_salary_min'] ?? $profile->expected_salary_min,
            'expected_salary_max' => $validated['expected_salary_max'] ?? $profile->expected_salary_max,
            'linkedin_url' => $validated['linkedin_url'] ?? $profile->linkedin_url,
            'portfolio_url' => $validated['portfolio_url'] ?? $profile->portfolio_url,
            'onboarding_step' => 2,
        ]);

        if (array_key_exists('skills', $validated)) {
            $user->skills()->sync($validated['skills'] ?? []);
        }

        if (array_key_exists('languages', $validated)) {
            $user->languages()->sync($validated['languages'] ?? []);
        }
    }

    private function updateCompanyStepTwo(CompanyProfile $profile, array $validated): void
    {
        $profile->update([
            'website' => $validated['website'] ?? $profile->website,
            'company_size' => $validated['company_size'] ?? $profile->company_size,
            'industry' => $validated['industry'] ?? $profile->industry,
            'location' => $validated['location'] ?? $profile->location,
            'about' => $validated['about'] ?? $profile->about,
            'onboarding_step' => 2,
        ]);
    }

    private function updatePersonStepThree(RegisterStepThreeRequest $request, User $user, array $validated): void
    {
        if ($request->hasFile('profile_photo')) {
            $user->update([
                'profile_photo' => $request->file('profile_photo')->store('profile-photos', 'public'),
            ]);
        }

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $path = $file->store('documents/cv', 'public');

            // Replace any existing primary CV
            $user->documents()->where('type', 'cv')->where('is_primary', true)->delete();

            Document::create([
                'user_id' => $user->id,
                'type' => 'cv',
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'is_primary' => true,
            ]);
        }

        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $certificate) {
                $path = $certificate->store('documents/certificates', 'public');

                Document::create([
                    'user_id' => $user->id,
                    'type' => 'certificate',
                    'file_path' => $path,
                    'file_name' => $certificate->getClientOriginalName(),
                    'mime_type' => $certificate->getMimeType(),
                    'file_size' => $certificate->getSize(),
                    'is_primary' => false,
                ]);
            }
        }

        $user->personProfile->update([
            'about' => $validated['about'] ?? $user->personProfile->about,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ]);

        if (array_key_exists('interests', $validated)) {
            $user->interests()->sync($validated['interests'] ?? []);
        }
    }

    private function updateCompanyStepThree(RegisterStepThreeRequest $request, CompanyProfile $profile, array $validated): void
    {
        $attributes = [
            'about' => $validated['about'] ?? $profile->about,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ];

        if ($request->hasFile('logo')) {
            $attributes['logo'] = $request->file('logo')->store('company-logos', 'public');
        }

        $profile->update($attributes);
    }

    public function changePassword(User $user, array $validated): void
    {
        if (! Hash::check($validated['old_password'], $user->password)) {
            throw ValidationException::withMessages([
                'old_password' => ['The provided old password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);
    }

    public function sendRegisterStep1Email(User $user): void
    {
        Mail::to($user->email)->queue(new RegisterStep1($user));
    }

    public function sendRegisterCompleteEmail(User $user): void
    {
        Mail::to($user->email)->queue(new RegisterComplete($user));
    }

    public function sendEmailVerification(User $user): void
    {
        if (! $user->hasVerifiedEmail()) {
            $user->notify(new VerifyEmailNotification);
        }
    }

    public function loadUserProfiles(User $user): User
    {
        return $user->loadMissing([
            'personProfile',
            'companyProfile',
            'skills',
            'languages',
            'interests',
            'documents',
        ]);
    }
}
