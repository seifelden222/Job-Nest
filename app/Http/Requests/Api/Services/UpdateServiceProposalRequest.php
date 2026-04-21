<?php

namespace App\Http\Requests\Api\Services;

use App\Models\ServiceProposal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ServiceProposal|null $serviceProposal */
        $serviceProposal = $this->route('serviceProposal');

        if (! $serviceProposal instanceof ServiceProposal || $this->user() === null) {
            return false;
        }

        $isOwner = (int) $serviceProposal->serviceRequest->user_id === (int) $this->user()->id;
        $isProposer = (int) $serviceProposal->user_id === (int) $this->user()->id;

        return $isOwner || $isProposer;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['submitted', 'accepted', 'rejected', 'withdrawn'])],
        ];
    }
}
