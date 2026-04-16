<?php

namespace App\Services\Auth;

use App\Models\CompanyProfile;
use App\Models\PersonProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @param  array<string, mixed>  $validated
     * @return array{user: User, token: string}
     */
    public function registerStepOne(array $validated): array
    {
        return DB::transaction(function () use ($validated): array {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'account_type' => $validated['account_type'],
                'status' => 'active',
            ]);

            $this->createProfileForUser($user, $validated);

            return [
                'user' => $this->loadUserRelations($user),
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function registerStepTwo(User $user, array $validated): User
    {
        return DB::transaction(function () use ($user, $validated): User {
            if ($user->isPerson()) {
                $this->updatePersonStepTwo($user->personProfile()->firstOrFail(), $validated);
            }

            if ($user->isCompany()) {
                $this->updateCompanyStepTwo($user->companyProfile()->firstOrFail(), $validated);
            }

            return $this->refreshUser($user);
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, UploadedFile>  $certificates
     */
    public function registerStepThree(
        User $user,
        array $validated,
        ?UploadedFile $profilePhoto = null,
        ?UploadedFile $cv = null,
        array $certificates = [],
        ?UploadedFile $logo = null,
    ): User {
        return DB::transaction(function () use ($user, $validated, $profilePhoto, $cv, $certificates, $logo): User {
            if ($user->isPerson()) {
                $this->updatePersonStepThree(
                    user: $user,
                    profile: $user->personProfile()->firstOrFail(),
                    validated: $validated,
                    profilePhoto: $profilePhoto,
                    cv: $cv,
                    certificates: $certificates,
                );
            }

            if ($user->isCompany()) {
                $this->updateCompanyStepThree(
                    profile: $user->companyProfile()->firstOrFail(),
                    validated: $validated,
                    logo: $logo,
                );
            }

            return $this->refreshUser($user);
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{user: User, token: string}
     */
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
            'user' => $this->loadUserRelations($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ];
    }

    public function me(User $user): User
    {
        return $this->loadUserRelations($user);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function createProfileForUser(User $user, array $validated): void
    {
        if ($user->isPerson()) {
            $user->personProfile()->create([
                'university' => $validated['university'] ?? null,
                'major' => $validated['major'] ?? null,
                'onboarding_step' => 1,
                'is_profile_completed' => false,
            ]);

            return;
        }

        $user->companyProfile()->create([
            'company_name' => $validated['company_name'],
            'website' => $validated['website'] ?? null,
            'company_size' => $validated['company_size'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'location' => $validated['location'] ?? null,
            'onboarding_step' => 1,
            'is_profile_completed' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
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

    /**
     * @param  array<string, mixed>  $validated
     */
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

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, UploadedFile>  $certificates
     */
    private function updatePersonStepThree(
        User $user,
        PersonProfile $profile,
        array $validated,
        ?UploadedFile $profilePhoto,
        ?UploadedFile $cv,
        array $certificates,
    ): void {
        if ($profilePhoto !== null) {
            $user->update([
                'profile_photo' => $profilePhoto->store('profile-photos', 'public'),
            ]);
        }

        if ($cv !== null) {
            $cv->store('documents/cv', 'public');
        }

        foreach ($certificates as $certificate) {
            $certificate->store('documents/certificates', 'public');
        }

        $profile->update([
            'about' => $validated['about'] ?? $profile->about,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function updateCompanyStepThree(CompanyProfile $profile, array $validated, ?UploadedFile $logo): void
    {
        $attributes = [
            'about' => $validated['about'] ?? $profile->about,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ];

        if ($logo !== null) {
            $attributes['logo'] = $logo->store('company-logos', 'public');
        }

        $profile->update($attributes);
    }

    private function loadUserRelations(User $user): User
    {
        return $user->loadMissing(['personProfile', 'companyProfile']);
    }

    private function refreshUser(User $user): User
    {
        return User::query()
            ->with(['personProfile', 'companyProfile'])
            ->findOrFail($user->getKey());
    }
}
