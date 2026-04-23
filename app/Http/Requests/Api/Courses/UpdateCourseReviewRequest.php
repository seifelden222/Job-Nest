<?php

namespace App\Http\Requests\Api\Courses;

use App\Models\CourseReview;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var CourseReview|null $courseReview */
        $courseReview = $this->route('courseReview');

        return $courseReview instanceof CourseReview
            && $this->user()?->can('update', $courseReview) === true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'comment' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
