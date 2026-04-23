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

        return $serviceProposal instanceof ServiceProposal
            && $this->user()?->can('update', $serviceProposal) === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['submitted', 'accepted', 'rejected', 'withdrawn'])],
        ];
    }
}
