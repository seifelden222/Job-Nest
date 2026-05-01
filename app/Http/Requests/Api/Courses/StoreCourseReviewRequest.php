<?php

namespace App\Http\Requests\Api\Courses;

use App\Http\Requests\Concerns\HasSourceLanguage;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourseReviewRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return $course instanceof Course
            && $this->user()?->can('create', [CourseReview::class, $course]) === true;
    }

    public function rules(): array
    {
        return array_merge([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['comment'];
    }
}
