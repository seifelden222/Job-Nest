<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Str;

class UserAiPayloadBuilder
{
    public function __construct(private readonly UserAiContextBuilder $userAiContextBuilder) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $user->loadMissing([
            'personProfile',
            'skills:id,name',
        ]);

        return [
            'user_name' => (string) $user->name,
            'user_skills' => $user->skills->pluck('name')->filter()->implode('|'),
            'role' => $this->role($user),
            'user_location' => 'Unknown',
            'experience_years' => $this->userAiContextBuilder->experienceYears($user),
            'preferred_job_type' => $this->userAiContextBuilder->preferredJobType($user),
            'expected_salary_egp' => $this->userAiContextBuilder->expectedSalaryRange($user),
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredFields(): array
    {
        return ['user_name', 'user_skills', 'role'];
    }

    private function role(User $user): string
    {
        $profile = $user->personProfile;

        if ($profile?->current_job_title) {
            return (string) $profile->current_job_title;
        }

        if ($profile?->employment_status) {
            return Str::title(str_replace('_', ' ', (string) $profile->employment_status));
        }

        if ($profile?->university || $profile?->major) {
            return 'Student';
        }

        return 'Person';
    }
}
