<?php

namespace App\Http\Requests\Api\Courses;

use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return $course instanceof Course
            && $this->user()?->can('create', [CourseReview::class, $course]) === true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
