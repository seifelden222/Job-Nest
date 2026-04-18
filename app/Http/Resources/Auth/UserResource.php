<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->hasVerifiedEmail(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'phone' => $this->phone,
            'account_type' => $this->account_type,
            'profile_photo' => $this->profile_photo
                ? Storage::url($this->profile_photo)
                : null,
            'status' => $this->status,
            'person_profile' => $this->when(
                $this->isPerson(),
                fn () => new PersonProfileResource($this->personProfile),
            ),
            'company_profile' => $this->when(
                $this->isCompany(),
                fn () => new CompanyProfileResource($this->companyProfile),
            ),
            'skills' => $this->when(
                $this->isPerson(),
                fn () => $this->skills->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
            ),
            'languages' => $this->when(
                $this->isPerson(),
                fn () => $this->languages->map(fn ($l) => ['id' => $l->id, 'name' => $l->name])->values(),
            ),
            'interests' => $this->when(
                $this->isPerson(),
                fn () => $this->interests->map(fn ($i) => ['id' => $i->id, 'name' => $i->name])->values(),
            ),
            'documents' => $this->when(
                $this->isPerson(),
                fn () => DocumentResource::collection($this->documents),
            ),
        ];
    }
}
