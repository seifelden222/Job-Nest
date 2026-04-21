<?php

namespace App\Http\Requests\Api\Applications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPerson() === true;
    }

    public function rules(): array
    {
        return [
            'cv_document_id' => [
                'nullable',
                'integer',
                Rule::exists('documents', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('type', 'cv');
                }),
            ],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
