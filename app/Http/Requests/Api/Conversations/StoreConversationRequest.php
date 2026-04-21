<?php

namespace App\Http\Requests\Api\Conversations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['direct', 'application'])],
            'participant_id' => [
                Rule::requiredIf($this->input('type') === 'direct'),
                'nullable',
                'integer',
                'exists:users,id',
                'different:user_id',
            ],
            'application_id' => [
                Rule::requiredIf($this->input('type') === 'application'),
                'nullable',
                'integer',
                'exists:applications,id',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()?->id,
        ]);
    }
}
