<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $common = [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];

        $person = [
            'about' => ['sometimes', 'nullable', 'string'],
            'university' => ['sometimes', 'nullable', 'string', 'max:255'],
            'major' => ['sometimes', 'nullable', 'string', 'max:255'],
            'employment_status' => ['sometimes', 'nullable', 'string', 'max:50'],
            'employment_type' => ['sometimes', 'nullable', 'string', 'max:50'],
            'current_job_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'portfolio_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'preferred_work_location' => ['sometimes', 'nullable', 'string', 'max:100'],
            'expected_salary_min' => ['sometimes', 'nullable', 'numeric'],
            'expected_salary_max' => ['sometimes', 'nullable', 'numeric'],
        ];

        $company = [
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'company_size' => ['sometimes', 'nullable', 'string', 'max:100'],
            'industry' => ['sometimes', 'nullable', 'string', 'max:255'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'about' => ['sometimes', 'nullable', 'string'],
        ];

        // Decide which rules apply based on authenticated user's account type if available
        $accountType = optional($this->user())->account_type;

        if ($accountType === 'company') {
            return array_merge($common, $company);
        }

        // default to person rules
        return array_merge($common, $person);
    }
}
