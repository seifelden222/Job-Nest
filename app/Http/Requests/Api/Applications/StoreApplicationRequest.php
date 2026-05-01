<?php

namespace App\Http\Requests\Api\Applications;

use App\Http\Requests\Concerns\HasSourceLanguage;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    use HasSourceLanguage;

    public function authorize(): bool
    {
        /** @var Job|null $job */
        $job = $this->route('job');

        return $job instanceof Job
            && $this->user()?->can('create', [Application::class, $job]) === true;
    }

    public function rules(): array
    {
        return array_merge([
            'cv_document_id' => [
                'nullable',
                'integer',
                Rule::exists('documents', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()->id)
                        ->where('type', 'cv');
                }),
            ],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
        ], $this->sourceLanguageRules(true));
    }

    protected function translatableFields(): array
    {
        return ['cover_letter'];
    }
}
