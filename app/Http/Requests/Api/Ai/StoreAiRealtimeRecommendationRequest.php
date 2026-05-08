<?php

namespace App\Http\Requests\Api\Ai;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiRealtimeRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_skills' => ['nullable', 'string'],
            'cv_summary' => ['nullable', 'string'],
            'user_location' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'preferred_job_type' => ['nullable', 'string', 'max:255'],
            'expected_salary_egp' => ['nullable', 'string', 'max:255'],
            'top_n' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
