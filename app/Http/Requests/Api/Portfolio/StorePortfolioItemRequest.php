<?php

namespace App\Http\Requests\Api\Portfolio;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'started_at' => ['nullable', 'date', 'before_or_equal:today'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'status' => ['nullable', 'string', 'in:in_progress,completed,archived'],
        ];
    }
}
