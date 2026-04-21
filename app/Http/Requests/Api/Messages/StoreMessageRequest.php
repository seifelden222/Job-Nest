<?php

namespace App\Http\Requests\Api\Messages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message_type' => ['nullable', Rule::in(['text', 'file', 'system'])],
            'body' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
