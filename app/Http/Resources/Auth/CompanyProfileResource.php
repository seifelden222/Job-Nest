<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'company_name' => $this->company_name,
            'website' => $this->website,
            'company_size' => $this->company_size,
            'industry' => $this->industry,
            'location' => $this->location,
            'about' => $this->about,
            'logo' => $this->logo
                ? (Str::startsWith(Storage::url($this->logo), ['http://', 'https://'])
                    ? Storage::url($this->logo)
                    : url(Storage::url($this->logo)))
                : null,
            'onboarding_step' => $this->onboarding_step,
            'is_profile_completed' => $this->is_profile_completed,
        ];
    }
}
