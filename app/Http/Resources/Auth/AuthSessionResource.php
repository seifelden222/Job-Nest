<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'name' => data_get($this->resource, 'name'),
            'current' => (bool) data_get($this->resource, 'current', false),
            'abilities' => data_get($this->resource, 'abilities', []),
            'last_used_at' => data_get($this->resource, 'last_used_at'),
            'created_at' => data_get($this->resource, 'created_at'),
            'expires_at' => data_get($this->resource, 'expires_at'),
        ];
    }
}
