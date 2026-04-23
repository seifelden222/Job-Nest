<?php

namespace App\Http\Requests\Api\Services;

use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ServiceRequest|null $serviceRequest */
        $serviceRequest = $this->route('serviceRequest');

        return $serviceRequest instanceof ServiceRequest
            && $this->user()?->can('create', [ServiceProposal::class, $serviceRequest]) === true;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string'],
            'proposed_budget' => ['nullable', 'numeric', 'min:0'],
            'delivery_days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
