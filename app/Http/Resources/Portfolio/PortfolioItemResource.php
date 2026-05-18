<?php

namespace App\Http\Resources\Portfolio;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PortfolioItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'project_url' => $this->project_url,
            'github_url' => $this->github_url,
            'thumbnail_url' => $this->thumbnail ? Storage::url($this->thumbnail) : null,
            'technologies' => $this->technologies ?? [],
            'role' => $this->role,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_featured' => (bool) $this->is_featured,
            'is_public' => (bool) $this->is_public,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
