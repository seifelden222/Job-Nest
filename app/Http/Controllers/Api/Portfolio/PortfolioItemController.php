<?php

namespace App\Http\Controllers\Api\Portfolio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portfolio\StorePortfolioItemRequest;
use App\Http\Requests\Api\Portfolio\UpdatePortfolioItemRequest;
use App\Http\Resources\Portfolio\PortfolioItemResource;
use App\Models\PortfolioItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortfolioItemController extends Controller
{
    /**
     * Public listing — returns all portfolio items for a given user (via query param) or all items.
     */
    public function index(Request $request): JsonResponse
    {
        $items = PortfolioItem::query()
            ->when($request->query('user_id'), fn ($q, $userId) => $q->where('user_id', $userId))
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Portfolio items fetched successfully.',
            'data' => PortfolioItemResource::collection($items)->response()->getData(true),
        ]);
    }

    public function store(StorePortfolioItemRequest $request): JsonResponse
    {
        $this->authorize('create', PortfolioItem::class);

        $user = $request->user();
        $validated = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('portfolio', 'public');
        }

        $item = PortfolioItem::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'live_url' => $validated['live_url'] ?? null,
            'image_path' => $imagePath,
            'started_at' => $validated['started_at'] ?? null,
            'completed_at' => $validated['completed_at'] ?? null,
            'status' => $validated['status'] ?? 'completed',
        ]);

        return response()->json([
            'message' => 'Portfolio item created successfully.',
            'data' => new PortfolioItemResource($item),
        ], 201);
    }

    public function show(PortfolioItem $portfolio): JsonResponse
    {
        return response()->json([
            'message' => 'Portfolio item fetched successfully.',
            'data' => new PortfolioItemResource($portfolio),
        ]);
    }

    public function update(UpdatePortfolioItemRequest $request, PortfolioItem $portfolio): JsonResponse
    {
        $this->authorize('update', $portfolio);

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($portfolio->image_path && Storage::disk('public')->exists($portfolio->image_path)) {
                Storage::disk('public')->delete($portfolio->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('portfolio', 'public');
        }

        unset($validated['image']);

        $portfolio->update($validated);

        return response()->json([
            'message' => 'Portfolio item updated successfully.',
            'data' => new PortfolioItemResource($portfolio->fresh()),
        ]);
    }

    public function destroy(PortfolioItem $portfolio): JsonResponse
    {
        $this->authorize('delete', $portfolio);

        if ($portfolio->image_path && Storage::disk('public')->exists($portfolio->image_path)) {
            Storage::disk('public')->delete($portfolio->image_path);
        }

        $portfolio->delete();

        return response()->json([
            'message' => 'Portfolio item deleted successfully.',
        ]);
    }

    /**
     * Returns the authenticated user's own portfolio items.
     */
    public function myPortfolio(Request $request): JsonResponse
    {
        $items = $request->user()
            ->portfolioItems()
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'My portfolio items fetched successfully.',
            'data' => PortfolioItemResource::collection($items)->response()->getData(true),
        ]);
    }
}
