<?php

namespace App\Http\Controllers\Api\Portfolio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portfolio\StorePortfolioItemRequest;
use App\Http\Requests\Api\Portfolio\UpdatePortfolioItemRequest;
use App\Http\Resources\Portfolio\PortfolioItemResource;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Services\Translation\ContentTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = PortfolioItem::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Portfolio items fetched successfully.',
            'data' => PortfolioItemResource::collection($items),
        ]);
    }

    public function publicIndex(Request $request, User $user): JsonResponse
    {
        $items = $user->portfolioItems()
            ->where('is_public', true)
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'User portfolio fetched successfully.',
            'data' => PortfolioItemResource::collection($items),
        ]);
    }

    public function store(StorePortfolioItemRequest $request, ContentTranslationService $translationService): JsonResponse
    {
        $this->authorize('create', PortfolioItem::class);

        $payload = $request->validated();

        if ($request->filled('source_language')) {
            $payload = $translationService->translatePayload($payload, ['title', 'description'], (string) $request->validated('source_language'));
        }

        if ($request->hasFile('thumbnail')) {
            $payload['thumbnail'] = $request->file('thumbnail')->store('portfolio/thumbnails', 'public');
        }

        $payload['user_id'] = $request->user()->id;

        $item = PortfolioItem::create($payload);

        return response()->json([
            'message' => 'Portfolio item created successfully.',
            'data' => new PortfolioItemResource($item->fresh()),
        ], 201);
    }

    public function show(Request $request, PortfolioItem $portfolioItem): JsonResponse
    {
        $this->authorize('view', $portfolioItem);

        return response()->json([
            'message' => 'Portfolio item fetched successfully.',
            'data' => new PortfolioItemResource($portfolioItem->load('user')),
        ]);
    }

    public function update(UpdatePortfolioItemRequest $request, PortfolioItem $portfolioItem, ContentTranslationService $translationService): JsonResponse
    {
        $this->authorize('update', $portfolioItem);

        $payload = $request->validated();

        if ($request->filled('source_language')) {
            $payload = $translationService->translatePayload($payload, ['title', 'description'], (string) $request->validated('source_language'));
        }

        if ($request->hasFile('thumbnail')) {
            if ($portfolioItem->thumbnail && Storage::disk('public')->exists($portfolioItem->thumbnail)) {
                Storage::disk('public')->delete($portfolioItem->thumbnail);
            }

            $payload['thumbnail'] = $request->file('thumbnail')->store('portfolio/thumbnails', 'public');
        }

        $portfolioItem->update($payload);

        return response()->json([
            'message' => 'Portfolio item updated successfully.',
            'data' => new PortfolioItemResource($portfolioItem->fresh()),
        ]);
    }

    public function destroy(Request $request, PortfolioItem $portfolioItem): JsonResponse
    {
        $this->authorize('delete', $portfolioItem);

        if ($portfolioItem->thumbnail && Storage::disk('public')->exists($portfolioItem->thumbnail)) {
            Storage::disk('public')->delete($portfolioItem->thumbnail);
        }

        $portfolioItem->delete();

        return response()->json([
            'message' => 'Portfolio item deleted successfully.',
        ]);
    }
}
