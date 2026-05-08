<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'] ?? null,
            'type' => $this['type'] ?? 'unknown',
            'title' => $this['title'] ?? null,
            'description' => $this['description'] ?? null,
            'score' => $this['score'] ?? null,
            'match_percentage' => $this['match_percentage'] ?? null,
            'reason' => $this['reason'] ?? null,
            'location' => $this['location'] ?? null,
            'employment_type' => $this['employment_type'] ?? null,
            'experience_level' => $this['experience_level'] ?? null,
            'salary' => $this['salary'] ?? [
                'min' => null,
                'max' => null,
                'currency' => null,
            ],
            'price' => $this['price'] ?? [
                'amount' => null,
                'currency' => null,
            ],
            'company' => $this['company'] ?? null,
            'owner' => $this['owner'] ?? null,
            'category' => $this['category'] ?? null,
            'skills' => $this['skills'] ?? [],
            'interaction' => $this['interaction'] ?? [],
        ];
    }
}
