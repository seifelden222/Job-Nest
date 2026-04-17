<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterStepOneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountType = $this->input('account_type');

        $rules = [
            'account_type' => ['required', Rule::in(['person', 'company'])],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];

        if ($accountType === 'person') {
            $rules['university'] = ['required', 'string', 'max:255'];
            $rules['major'] = ['required', 'string', 'max:255'];
        }

        if ($accountType === 'company') {
            $rules['company_name'] = ['required', 'string', 'max:255'];
            $rules['website'] = ['nullable', 'url', 'max:255'];
            $rules['company_size'] = ['nullable', 'string', 'max:100'];
            $rules['industry'] = ['nullable', 'string', 'max:255'];
            $rules['location'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'account_type.required' => 'Account type is required.',
            'account_type.in' => 'Account type must be either person or company.',
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'password.confirmed' => 'Password confirmation does not match.',
            'university.required' => 'University is required for person accounts.',
            'major.required' => 'Major is required for person accounts.',
            'company_name.required' => 'Company name is required for company accounts.',
        ];
    }
}
