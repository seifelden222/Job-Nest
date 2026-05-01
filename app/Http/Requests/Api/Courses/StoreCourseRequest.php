<?php

namespace App\Http\Requests\Api\Courses;

use App\Http\Requests\Concerns\HasSourceLanguage;
use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Course::class) === true;
    }

    public function rules(): array
    {
        return array_merge([
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 'course')),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:courses,slug'],
            'thumbnail' => ['nullable', 'file', 'image', 'max:5120'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'course_overview' => ['nullable', 'string'],
            'what_you_learn' => ['nullable', 'string'],
            'level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'delivery_mode' => ['nullable', Rule::in(['online', 'offline', 'hybrid'])],
            'language' => ['nullable', 'string', 'max:20'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'seats_count' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(['draft', 'published', 'closed', 'archived'])],
            'is_active' => ['nullable', 'boolean'],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
        ], $this->sourceLanguageRules());
    }

    protected function translatableFields(): array
    {
        return ['title', 'short_description', 'description', 'course_overview', 'what_you_learn'];
    }
}
