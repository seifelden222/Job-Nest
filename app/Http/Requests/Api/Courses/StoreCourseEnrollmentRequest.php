<?php

namespace App\Http\Requests\Api\Courses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['nullable', Rule::in(['card', 'cash', 'free'])],
        ];
    }
}
