<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_type' => ['required', Rule::in(['company'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'company_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'company_size' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_type.required' => 'Account type is required.',
            'account_type.in' => 'Account type must be company for this endpoint.',
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'company_name.required' => 'Company name is required for company accounts.',
        ];
    }
}
