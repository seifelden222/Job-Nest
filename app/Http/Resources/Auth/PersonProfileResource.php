<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'university' => $this->university,
            'major' => $this->major,
            'current_job_title' => $this->current_job_title,
            'employment_status' => $this->employment_status,
            'expected_salary_min' => $this->expected_salary_min,
            'expected_salary_max' => $this->expected_salary_max,
            'preferred_work_location' => $this->preferred_work_location,
            'linkedin_url' => $this->linkedin_url,
            'portfolio_url' => $this->portfolio_url,
            'about' => $this->about,
            'onboarding_step' => $this->onboarding_step,
            'is_profile_completed' => $this->is_profile_completed,
        ];
    }
}
