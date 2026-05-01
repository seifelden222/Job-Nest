<?php

namespace App\Http\Requests\Api\Categories;

use App\Http\Requests\Concerns\HasSourceLanguage;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return $category instanceof Category
            && $this->user()?->can('update', $category) === true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['job', 'course', 'service'])],
            'description' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['name', 'description'];
    }
}
