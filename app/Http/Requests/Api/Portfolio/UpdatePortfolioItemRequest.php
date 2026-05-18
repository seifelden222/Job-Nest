<?php

namespace App\Http\Requests\Api\Portfolio;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'live_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'started_at' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'completed_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:started_at'],
            'status' => ['sometimes', 'nullable', 'string', 'in:in_progress,completed,archived'],
        ];
    }
}
