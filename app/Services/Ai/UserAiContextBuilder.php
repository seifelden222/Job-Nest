<?php

namespace App\Services\Ai;

use App\Models\SavedItem;
use App\Models\User;
use Illuminate\Support\Str;

class UserAiContextBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $user->loadMissing([
            'skills:id,name',
            'languages:id,name',
            'interests:id,name',
            'personProfile',
            'companyProfile',
        ]);

        $savedItems = SavedItem::query()
            ->whereBelongsTo($user)
            ->latest('id')
            ->limit(20)
            ->get(['type', 'target_id']);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'account_type' => $user->account_type,
                'status' => $user->status,
            ],
            'profile' => $user->isPerson()
                ? $this->personProfileContext($user)
                : $this->companyProfileContext($user),
            'skills' => $user->skills
                ->map(fn ($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ])
                ->values()
                ->all(),
            'languages' => $user->languages
                ->map(fn ($language) => [
                    'id' => $language->id,
                    'name' => $language->name,
                ])
                ->values()
                ->all(),
            'interests' => $user->interests
                ->map(fn ($interest) => [
                    'id' => $interest->id,
                    'name' => $interest->name,
                ])
                ->values()
                ->all(),
            'signals' => [
                'saved_items' => [
                    'jobs' => $savedItems->where('type', 'job')->pluck('target_id')->values()->all(),
                    'courses' => $savedItems->where('type', 'course')->pluck('target_id')->values()->all(),
                    'service_requests' => $savedItems->where('type', 'service_request')->pluck('target_id')->values()->all(),
                ],
                'applications' => [
                    'count' => $user->applications()->count(),
                    'job_ids' => $user->applications()->latest('id')->limit(20)->pluck('job_id')->filter()->values()->all(),
                ],
                'course_enrollments' => [
                    'count' => $user->courseEnrollments()->count(),
                    'course_ids' => $user->courseEnrollments()->latest('id')->limit(20)->pluck('course_id')->filter()->values()->all(),
                ],
                'messages_sent_count' => $user->messages()->count(),
                'owned_job_ids' => $user->jobs()->latest('id')->limit(20)->pluck('id')->values()->all(),
                'owned_course_ids' => $user->courses()->latest('id')->limit(20)->pluck('id')->values()->all(),
                'owned_service_request_ids' => $user->serviceRequests()->latest('id')->limit(20)->pluck('id')->values()->all(),
            ],
        ];
    }

    public function experienceYears(User $user): int
    {
        return 0;
    }

    public function preferredJobType(User $user): string
    {
        $user->loadMissing('personProfile');

        if (! $user->isPerson() || $user->personProfile === null) {
            return '';
        }

        return collect([
            $user->personProfile->preferred_work_location
                ? Str::title((string) $user->personProfile->preferred_work_location)
                : null,
            $user->personProfile->employment_type
                ? Str::title(str_replace('_', ' ', (string) $user->personProfile->employment_type))
                : null,
        ])->filter()->unique()->values()->implode('|');
    }

    public function expectedSalaryRange(User $user): string
    {
        $user->loadMissing('personProfile');

        if (! $user->isPerson() || $user->personProfile === null) {
            return '0-0';
        }

        $minimum = is_numeric($user->personProfile->expected_salary_min)
            ? (int) round((float) $user->personProfile->expected_salary_min)
            : 0;
        $maximum = is_numeric($user->personProfile->expected_salary_max)
            ? (int) round((float) $user->personProfile->expected_salary_max)
            : 0;

        return $minimum.'-'.$maximum;
    }

    public function cvSummary(User $user): string
    {
        $user->loadMissing('personProfile');

        return (string) ($user->personProfile?->about ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    private function personProfileContext(User $user): array
    {
        $profile = $user->personProfile;

        return [
            'preferred_work_location' => $profile?->preferred_work_location,
            'employment_type' => $profile?->employment_type,
            'employment_status' => $profile?->employment_status,
            'current_job_title' => $profile?->current_job_title,
            'company_name' => $profile?->company_name,
            'expected_salary_min' => $profile?->expected_salary_min,
            'expected_salary_max' => $profile?->expected_salary_max,
            'about' => $profile?->about,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function companyProfileContext(User $user): array
    {
        $profile = $user->companyProfile;

        return [
            'company_name' => $profile?->company_name,
            'industry' => $profile?->industry,
            'location' => $profile?->location,
            'company_size' => $profile?->company_size,
            'about' => $profile?->about,
        ];
    }
}
