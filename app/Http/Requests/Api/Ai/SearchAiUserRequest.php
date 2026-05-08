<?php

namespace App\Http\Requests\Api\Ai;

use Illuminate\Foundation\Http\FormRequest;

class SearchAiUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
