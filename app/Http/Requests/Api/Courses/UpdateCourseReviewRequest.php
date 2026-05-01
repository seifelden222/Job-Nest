<?php

namespace App\Http\Requests\Api\Courses;

use App\Http\Requests\Concerns\HasSourceLanguage;
use App\Models\CourseReview;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseReviewRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        /** @var CourseReview|null $courseReview */
        $courseReview = $this->route('courseReview');

        return $courseReview instanceof CourseReview
            && $this->user()?->can('update', $courseReview) === true;
    }

    public function rules(): array
    {
        return array_merge([
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'comment' => ['sometimes', 'nullable', 'string'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['comment'];
    }
}
