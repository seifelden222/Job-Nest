<?php

namespace App\Services\SavedItems;

use App\Enums\SavedItemType;
use App\Models\Course;
use App\Models\Job;
use App\Models\SavedItem;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class SavedItemTargetResolver
{
    /**
     * @param  EloquentCollection<int, SavedItem>  $savedItems
     */
    public function hydrateTargets(EloquentCollection $savedItems): void
    {
        if ($savedItems->isEmpty()) {
            return;
        }

        $targetsByType = collect(SavedItemType::cases())
            ->mapWithKeys(fn (SavedItemType $type): array => [
                $type->value => $this->fetchTargetsForType($savedItems, $type),
            ]);

        $savedItems->each(function (SavedItem $savedItem) use ($targetsByType): void {
            $type = $savedItem->type instanceof SavedItemType
                ? $savedItem->type
                : SavedItemType::from((string) $savedItem->type);

            $savedItem->setRelation(
                'target',
                $targetsByType->get($type->value)?->get($savedItem->target_id),
            );
        });
    }

    /**
     * @param  EloquentCollection<int, SavedItem>  $savedItems
     * @return Collection<int, Job|Course|ServiceRequest>
     */
    private function fetchTargetsForType(EloquentCollection $savedItems, SavedItemType $type): Collection
    {
        $targetIds = $savedItems
            ->filter(function (SavedItem $savedItem) use ($type): bool {
                $savedItemType = $savedItem->type instanceof SavedItemType
                    ? $savedItem->type
                    : SavedItemType::from((string) $savedItem->type);

                return $savedItemType === $type;
            })
            ->pluck('target_id')
            ->unique()
            ->values();

        if ($targetIds->isEmpty()) {
            return collect();
        }

        return match ($type) {
            SavedItemType::Job => Job::query()
                ->select(['id', 'company_id', 'title', 'location', 'employment_type', 'status', 'is_active'])
                ->whereIn('id', $targetIds)
                ->get()
                ->keyBy('id'),
            SavedItemType::Course => Course::query()
                ->select(['id', 'user_id', 'title', 'slug', 'price', 'currency', 'status', 'is_active'])
                ->whereIn('id', $targetIds)
                ->get()
                ->keyBy('id'),
            SavedItemType::ServiceRequest => ServiceRequest::query()
                ->select(['id', 'user_id', 'title', 'budget_min', 'budget_max', 'currency', 'status'])
                ->whereIn('id', $targetIds)
                ->get()
                ->keyBy('id'),
        };
    }
}
