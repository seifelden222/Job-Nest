<?php

namespace App\Http\Requests\Api\Courses;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return $course instanceof Course
            && $this->user()?->trainingProviderProfile?->id === $course->training_provider_id;
    }

    public function rules(): array
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return [
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 'course')),
            ],
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('courses', 'slug')->ignore($course?->id)],
            'thumbnail' => ['sometimes', 'nullable', 'file', 'image', 'max:5120'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'course_overview' => ['sometimes', 'nullable', 'string'],
            'what_you_learn' => ['sometimes', 'nullable', 'string'],
            'level' => ['sometimes', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'delivery_mode' => ['sometimes', Rule::in(['online', 'offline', 'hybrid'])],
            'language' => ['sometimes', 'nullable', 'string', 'max:20'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'duration_hours' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'seats_count' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'closed', 'archived'])],
            'is_active' => ['sometimes', 'boolean'],
            'skill_ids' => ['sometimes', 'array'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
        ];
    }
}
