<?php

namespace App\Http\Requests\Api\Messages;

use App\Http\Requests\Concerns\HasSourceLanguage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return array_merge([
            'message_type' => ['nullable', Rule::in(['text', 'file', 'system'])],
            'body' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:10240'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['body'];
    }
}
