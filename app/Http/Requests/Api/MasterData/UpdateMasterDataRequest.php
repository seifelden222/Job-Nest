<?php

namespace App\Http\Requests\Api\MasterData;

use App\Http\Requests\Concerns\HasSourceLanguage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMasterDataRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'name' => ['sometimes', 'string', 'max:255'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['name'];
    }
}
