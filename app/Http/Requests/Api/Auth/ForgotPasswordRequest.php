<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_or_phone' => [
                'required',
                'string',
                'max:255',
                Rule::when($this->input('method') === 'email', ['email']),
                Rule::when($this->input('method') === 'phone', ['regex:/^[0-9+\-\s()]+$/']),
            ],
            'method' => ['required', Rule::in(['email', 'phone'])],
        ];
    }

    public function messages(): array
    {
        return [
            'email_or_phone.required' => 'Email or phone is required.',
            'email_or_phone.email' => 'A valid email address is required when method is email.',
            'email_or_phone.regex' => 'A valid phone number is required when method is phone.',
            'method.required' => 'Reset method is required.',
            'method.in' => 'Method must be either email or phone.',
        ];
    }
}
