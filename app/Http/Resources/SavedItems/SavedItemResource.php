<?php

namespace App\Http\Resources\SavedItems;

use App\Enums\SavedItemType;
use App\Models\Course;
use App\Models\Job;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->type instanceof SavedItemType
            ? $this->type
            : SavedItemType::from((string) $this->type);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $type->value,
            'target_id' => $this->target_id,
            'target' => $this->formatTarget($type),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatTarget(SavedItemType $type): ?array
    {
        $target = $this->whenLoaded('target');

        if (! $target instanceof Job && ! $target instanceof Course && ! $target instanceof ServiceRequest) {
            return null;
        }

        return match ($type) {
            SavedItemType::Job => [
                'id' => $target->id,
                'title' => $target->title,
                'company_id' => $target->company_id,
                'location' => $target->location,
                'employment_type' => $target->employment_type,
                'status' => $target->status,
                'is_active' => $target->is_active,
            ],
            SavedItemType::Course => [
                'id' => $target->id,
                'title' => $target->title,
                'owner_id' => $target->user_id,
                'slug' => $target->slug,
                'price' => $target->price,
                'currency' => $target->currency,
                'status' => $target->status,
                'is_active' => $target->is_active,
            ],
            SavedItemType::ServiceRequest => [
                'id' => $target->id,
                'title' => $target->title,
                'owner_id' => $target->user_id,
                'budget_min' => $target->budget_min,
                'budget_max' => $target->budget_max,
                'currency' => $target->currency,
                'status' => $target->status,
            ],
        };
    }
}
