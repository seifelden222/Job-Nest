<?php

namespace App\Http\Requests\Api\MasterData;

use App\Http\Requests\Concerns\HasSourceLanguage;
use Illuminate\Foundation\Http\FormRequest;

class StoreMasterDataRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['required', 'string', 'max:255'],
        ], $this->sourceLanguageRules());
    }

    protected function translatableFields(): array
    {
        return ['name'];
    }
}
