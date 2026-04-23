<?php

namespace App\Http\Requests\Api\SavedItems;

use App\Enums\SavedItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestroySavedItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(SavedItemType::class)],
            'target_id' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => strtolower((string) $this->route('type')),
            'target_id' => (int) $this->route('targetId'),
        ]);
    }
}
