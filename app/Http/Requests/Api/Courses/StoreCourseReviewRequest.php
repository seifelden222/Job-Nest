<?php

namespace App\Http\Requests\Api\Courses;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
