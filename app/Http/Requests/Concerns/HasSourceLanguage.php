<?php

namespace App\Http\Requests\Concerns;

use App\Support\ApiLocale;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait HasSourceLanguage
{
    protected function sourceLanguageRules(bool $sometimes = false): array
    {
        return [
            'source_language' => [
                $sometimes ? 'sometimes' : 'required',
                Rule::in(ApiLocale::supported()),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->containsTranslatableInput()) {
                return;
            }

            if (! $this->filled('source_language')) {
                $validator->errors()->add('source_language', 'The source language field is required when translatable content is present.');
            }
        });
    }

    private function containsTranslatableInput(): bool
    {
        foreach ($this->translatableFields() as $field) {
            if ($this->exists($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    abstract protected function translatableFields(): array;
}
