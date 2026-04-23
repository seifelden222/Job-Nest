<?php

namespace App\Http\Requests\Api\Applications;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Application|null $application */
        $application = $this->route('application');

        return $application instanceof Application
            && $this->user()?->can('update', $application) === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([
                'submitted',
                'under_review',
                'shortlisted',
                'interview_scheduled',
                'offered',
                'accepted',
                'rejected',
                'withdrawn',
            ])],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
