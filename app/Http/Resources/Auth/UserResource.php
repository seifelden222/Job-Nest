<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'account_type' => $this->account_type,
            'profile_photo' => $this->profile_photo,
            'status' => $this->status,
            'person_profile' => $this->isPerson() ? new PersonProfileResource($this->personProfile) : null,
            'company_profile' => $this->isCompany() ? new CompanyProfileResource($this->companyProfile) : null,
        ];
    }
}
