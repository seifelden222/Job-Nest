<?php

namespace App\Http\Requests\Api\Services;

use App\Models\ServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ServiceRequest|null $serviceRequest */
        $serviceRequest = $this->route('serviceRequest');

        return $serviceRequest instanceof ServiceRequest
            && $this->user()?->can('update', $serviceRequest) === true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 'service')),
            ],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'budget_min' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'budget_max' => ['sometimes', 'nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'currency' => ['sometimes', 'nullable', 'string', 'max:10'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'delivery_mode' => ['sometimes', 'nullable', Rule::in(['online', 'offline', 'hybrid'])],
            'deadline' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(['open', 'in_progress', 'closed', 'cancelled'])],
            'skill_ids' => ['sometimes', 'array'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
        ];
    }
}
