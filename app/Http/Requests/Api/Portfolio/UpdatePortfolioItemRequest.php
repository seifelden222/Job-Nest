<?php

namespace App\Http\Requests\Api\Portfolio;

use App\Http\Requests\Concerns\HasSourceLanguage;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioItemRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        $item = $this->route('portfolio') ?? $this->route('portfolioItem');

        return $this->user()?->isActive() === true && $item !== null;
    }

    public function rules(): array
    {
        return array_merge([
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'project_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'github_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'thumbnail' => ['sometimes', 'nullable', 'file', 'image', 'max:5120'],
            'technologies' => ['sometimes', 'nullable', 'array'],
            'technologies.*' => ['string', 'max:100'],
            'role' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['title', 'description'];
    }
}
