<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterStepThreeRequest extends FormRequest
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
                'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
                'certificates' => ['nullable', 'array'],
                'certificates.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
                'about' => ['nullable', 'string', 'max:2000'],
                'interests' => ['nullable', 'array'],
                'interests.*' => ['integer', 'exists:interests,id'],
            ];
        }

        if ($accountType === 'company') {
            return [
                'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'about' => ['nullable', 'string', 'max:2000'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'cv.mimes' => 'CV must be a PDF or Word document.',
            'cv.max' => 'CV size must not exceed 5 MB.',
            'certificates.array' => 'Certificates must be sent as an array.',
            'certificates.*.mimes' => 'Each certificate must be a valid PDF, Word, or image file.',
            'certificates.*.max' => 'Each certificate must not exceed 5 MB.',
            'about.max' => 'About field must not exceed 2000 characters.',
            'interests.*.exists' => 'One or more selected interests are invalid.',
        ];
    }
}
