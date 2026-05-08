<?php

namespace App\Services\Ai;

use App\Models\User;

class RecommendationService
{
    public function __construct(
        private readonly ExternalAiClient $externalAiClient,
        private readonly UserAiContextBuilder $userAiContextBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function recommend(User $user, array $input): array
    {
        return $this->externalAiClient->recommend([
            'user_id' => $user->ai_user_id,
            'user_name' => $input['user_name'] ?? $user->name,
            'top_n' => $input['top_n'] ?? 10,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function recommendRealtime(User $user, array $input): array
    {
        return $this->externalAiClient->recommendRealtime([
            'user_skills' => $input['user_skills'] ?? $this->skillsString($user),
            'cv_summary' => $input['cv_summary'] ?? $this->userAiContextBuilder->cvSummary($user),
            'user_location' => $input['user_location'] ?? 'Unknown',
            'experience_years' => $input['experience_years'] ?? $this->userAiContextBuilder->experienceYears($user),
            'preferred_job_type' => $input['preferred_job_type'] ?? $this->userAiContextBuilder->preferredJobType($user),
            'expected_salary_egp' => $input['expected_salary_egp'] ?? $this->userAiContextBuilder->expectedSalaryRange($user),
            'top_n' => $input['top_n'] ?? 10,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function recommendCourses(User $user, array $input): array
    {
        return $this->externalAiClient->recommendCourses([
            'user_id' => $user->ai_user_id,
            'user_name' => $input['user_name'] ?? $user->name,
            'top_n' => $input['top_n'] ?? 10,
        ]);
    }

    private function skillsString(User $user): string
    {
        $user->loadMissing('skills:id,name');

        return $user->skills->pluck('name')->filter()->implode('|');
    }
}
