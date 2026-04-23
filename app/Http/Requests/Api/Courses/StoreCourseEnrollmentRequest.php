<?php

namespace App\Http\Requests\Api\Courses;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return $course instanceof Course
            && $this->user()?->can('create', [CourseEnrollment::class, $course]) === true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['nullable', Rule::in(['card', 'cash', 'free'])],
        ];
    }
}
