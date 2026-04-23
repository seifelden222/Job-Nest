<?php

namespace App\Http\Requests\Api\SavedItems;

use App\Enums\SavedItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSavedItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(SavedItemType::class)],
            'target_id' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('saved_items', 'target_id')->where(function ($query) {
                    return $query
                        ->where('user_id', $this->user()->id)
                        ->where('type', $this->string('type')->toString());
                }),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('type')) {
            $this->merge([
                'type' => strtolower((string) $this->input('type')),
            ]);
        }
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = $this->input('type');
                $targetId = $this->input('target_id');

                if (! is_string($type) || ! is_numeric((string) $targetId) || ! in_array($type, SavedItemType::values(), true)) {
                    return;
                }

                if (! DB::table(SavedItemType::from($type)->table())->where('id', (int) $targetId)->exists()) {
                    $validator->errors()->add('target_id', 'The selected target is invalid for the given type.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'target_id.unique' => 'This item is already saved.',
        ];
    }
}
