<?php

namespace App\Http\Requests\Api\Categories;

use App\Http\Requests\Concerns\HasSourceLanguage;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) === true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['job', 'course', 'service'])],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ], $this->sourceLanguageRules());
    }

    protected function translatableFields(): array
    {
        return ['name', 'description'];
    }
}
