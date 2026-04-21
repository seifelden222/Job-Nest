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

        if (! $application instanceof Application) {
            return false;
        }

        $user = $this->user();

        $isOwnerCompany = $user?->isCompany() === true
            && (int) $application->job->company_id === (int) $user->id;

        $isApplicant = (int) $application->user_id === (int) $user?->id;

        return $isOwnerCompany || $isApplicant;
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
