<?php

namespace App\Services\Auth;

use App\Http\Requests\Api\Auth\RegisterStepThreeRequest;
use App\Models\CompanyProfile;
use App\Models\PersonProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function registerStepOne(array $validated): array
    {
        return DB::transaction(function () use ($validated): array {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'account_type' => $validated['account_type'],
                'status' => 'active',
            ]);

            $this->createProfileForUser($user, $validated);

            return [
                'user' => $this->loadUserProfiles($user),
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        });
    }

    public function registerStepTwo(User $user, array $validated): User
    {
        return DB::transaction(function () use ($user, $validated): User {
            if ($user->isPerson()) {
                $this->updatePersonStepTwo($user->personProfile, $validated);
            }

            if ($user->isCompany()) {
                $this->updateCompanyStepTwo($user->companyProfile, $validated);
            }

            return $this->loadUserProfiles($user->fresh());
        });
    }

    public function registerStepThree(RegisterStepThreeRequest $request, User $user, array $validated): User
    {
        return DB::transaction(function () use ($request, $user, $validated): User {
            if ($user->isPerson()) {
                $this->updatePersonStepThree($request, $user, $validated);
            }

            if ($user->isCompany()) {
                $this->updateCompanyStepThree($request, $user->companyProfile, $validated);
            }

            return $this->loadUserProfiles($user->fresh());
        });
    }

    public function login(array $validated): array
    {
        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return [
            'user' => $this->loadUserProfiles($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
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
            $accessToken->delete();
        }
    }

    private function createProfileForUser(User $user, array $validated): void
    {
        if ($user->isPerson()) {
            PersonProfile::create([
                'user_id' => $user->id,
                'university' => $validated['university'] ?? null,
                'major' => $validated['major'] ?? null,
                'onboarding_step' => 1,
                'is_profile_completed' => false,
            ]);

            return;
        }

        CompanyProfile::create([
            'user_id' => $user->id,
            'company_name' => $validated['company_name'],
            'website' => $validated['website'] ?? null,
            'company_size' => $validated['company_size'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'location' => $validated['location'] ?? null,
            'onboarding_step' => 1,
            'is_profile_completed' => false,
        ]);
    }

    private function updatePersonStepTwo(PersonProfile $profile, array $validated): void
    {
        $profile->update([
            'employment_status' => $validated['employment_status'] ?? $profile->employment_status,
            'current_job_title' => $validated['current_job_title'] ?? $profile->current_job_title,
            'linkedin_url' => $validated['linkedin_url'] ?? $profile->linkedin_url,
            'portfolio_url' => $validated['portfolio_url'] ?? $profile->portfolio_url,
            'onboarding_step' => 2,
        ]);
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
            $request->file('cv')->store('documents/cv', 'public');
        }

        if ($request->hasFile('certificates')) {
            foreach ($request->file('certificates') as $certificate) {
                $certificate->store('documents/certificates', 'public');
            }
        }

        $user->personProfile->update([
            'about' => $validated['about'] ?? $user->personProfile->about,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ]);
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

    private function loadUserProfiles(User $user): User
    {
        return $user->loadMissing(['personProfile', 'companyProfile']);
    }
}
