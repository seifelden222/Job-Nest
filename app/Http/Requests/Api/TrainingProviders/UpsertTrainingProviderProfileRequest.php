<?php

namespace App\Http\Requests\Api\TrainingProviders;

use Illuminate\Foundation\Http\FormRequest;

class UpsertTrainingProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'provider_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string'],
            'logo' => ['nullable', 'file', 'image', 'max:5120'],
            'is_profile_completed' => ['nullable', 'boolean'],
        ];
    }
}
