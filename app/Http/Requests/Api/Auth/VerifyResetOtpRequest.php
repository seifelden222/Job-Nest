<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyResetOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email_or_phone' => ['required', 'string', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email_or_phone.required' => 'Email or phone is required.',
            'otp.required' => 'OTP is required.',
            'otp.digits' => 'OTP must be exactly 6 digits.',
        ];
    }
}
