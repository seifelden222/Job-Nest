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
                'current_job_title' => ['nullable', 'string', 'max:255'],
                'linkedin_url' => ['nullable', 'url', 'max:255'],
                'portfolio_url' => ['nullable', 'url', 'max:255'],
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
        ];
    }
}
