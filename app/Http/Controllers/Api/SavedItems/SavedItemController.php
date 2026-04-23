<?php

namespace App\Http\Controllers\Api\SavedItems;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SavedItems\CheckSavedItemRequest;
use App\Http\Requests\Api\SavedItems\DestroySavedItemRequest;
use App\Http\Requests\Api\SavedItems\IndexSavedItemRequest;
use App\Http\Requests\Api\SavedItems\StoreSavedItemRequest;
use App\Http\Resources\SavedItems\SavedItemResource;
use App\Models\SavedItem;
use App\Services\SavedItems\SavedItemTargetResolver;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;

class SavedItemController extends Controller
{
    public function __construct(private SavedItemTargetResolver $targetResolver) {}

    public function index(IndexSavedItemRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $savedItems = SavedItem::query()
            ->whereBelongsTo($request->user())
            ->when(
                $filters['type'] ?? null,
                fn ($query, string $type) => $query->where('type', $type),
            )
            ->latest()
            ->get();

        $this->targetResolver->hydrateTargets($savedItems);

        $data = SavedItemResource::collection($savedItems)->resolve($request);

        return response()->json([
            'message' => 'Saved items fetched successfully.',
            'data' => $data,
            'grouped_data' => collect($data)
                ->groupBy('type')
                ->map(fn ($items) => $items->values())
                ->all(),
            'filters' => [
                'type' => $filters['type'] ?? null,
            ],
        ]);
    }

    public function store(StoreSavedItemRequest $request): JsonResponse
    {
        $savedItem = SavedItem::query()->create([
            'user_id' => $request->user()->id,
            'type' => $request->validated('type'),
            'target_id' => $request->validated('target_id'),
        ]);

        $this->targetResolver->hydrateTargets(new EloquentCollection([$savedItem]));

        return response()->json([
            'message' => 'Item saved successfully.',
            'saved_item' => SavedItemResource::make($savedItem)->resolve($request),
        ], 201);
    }

    public function destroy(DestroySavedItemRequest $request, string $type, int $targetId): JsonResponse
    {
        $savedItem = SavedItem::query()
            ->whereBelongsTo($request->user())
            ->where('type', $type)
            ->where('target_id', $targetId)
            ->first();

        if ($savedItem === null) {
            return response()->json([
                'message' => 'Saved item not found.',
            ], 404);
        }

        $savedItem->delete();

        return response()->json([
            'message' => 'Saved item removed successfully.',
        ]);
    }

    public function check(CheckSavedItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $isSaved = SavedItem::query()
            ->whereBelongsTo($request->user())
            ->where('type', $validated['type'])
            ->where('target_id', $validated['target_id'])
            ->exists();

        return response()->json([
            'message' => 'Saved item status fetched successfully.',
            'data' => [
                'type' => $validated['type'],
                'target_id' => (int) $validated['target_id'],
                'is_saved' => $isSaved,
            ],
        ]);
    }
}
