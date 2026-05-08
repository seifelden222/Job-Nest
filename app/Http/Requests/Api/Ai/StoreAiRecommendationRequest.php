<?php

namespace App\Http\Requests\Api\Ai;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_name' => ['nullable', 'string', 'max:255'],
            'top_n' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
