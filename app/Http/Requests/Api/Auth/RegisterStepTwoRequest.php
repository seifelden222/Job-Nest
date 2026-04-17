<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStepTwoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $accountType = $user?->account_type;

        if ($accountType === 'person') {
            return [
                'employment_status' => ['nullable', 'string', 'max:100'],
                'employment_type' => ['nullable', 'string', 'max:100'],
                'current_job_title' => ['nullable', 'string', 'max:255'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'preferred_work_location' => ['nullable', 'string', 'in:onsite,remote,hybrid'],
                'expected_salary_min' => ['nullable', 'numeric', 'min:0'],
                'expected_salary_max' => ['nullable', 'numeric', 'min:0', 'gte:expected_salary_min'],
                'linkedin_url' => ['nullable', 'url', 'max:255'],
                'portfolio_url' => ['nullable', 'url', 'max:255'],
                'skills' => ['nullable', 'array'],
                'skills.*' => ['integer', 'exists:skills,id'],
                'languages' => ['nullable', 'array'],
                'languages.*' => ['integer', 'exists:languages,id'],
            ];
        }

        if ($accountType === 'company') {
            return [
                'website' => ['nullable', 'url', 'max:255'],
                'company_size' => ['nullable', 'string', 'max:100'],
                'industry' => ['nullable', 'string', 'max:255'],
                'location' => ['nullable', 'string', 'max:255'],
                'about' => ['nullable', 'string', 'max:2000'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'linkedin_url.url' => 'LinkedIn profile must be a valid URL.',
            'portfolio_url.url' => 'Portfolio website must be a valid URL.',
            'expected_salary_max.gte' => 'Max salary must be greater than or equal to min salary.',
            'preferred_work_location.in' => 'Work location must be onsite, remote, or hybrid.',
            'skills.*.exists' => 'One or more selected skills are invalid.',
            'languages.*.exists' => 'One or more selected languages are invalid.',
        ];
    }
}
