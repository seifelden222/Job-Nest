<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Categories\StoreCategoryRequest;
use App\Http\Requests\Api\Categories\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::query()
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
            ->when($request->boolean('active_only', true), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Categories fetched successfully.',
            'data' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['slug'] = $this->buildSlug($payload['slug'] ?? $payload['name'], $payload['type']);

        $category = Category::create($payload);

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'message' => 'Category fetched successfully.',
            'data' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $payload = $request->validated();

        if (array_key_exists('slug', $payload) || array_key_exists('name', $payload) || array_key_exists('type', $payload)) {
            $payload['slug'] = $this->buildSlug(
                $payload['slug'] ?? $payload['name'] ?? $category->name,
                $payload['type'] ?? $category->type,
                $category->id,
            );
        }

        $category->update($payload);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category->fresh(),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    private function buildSlug(string $value, string $type, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug !== '' ? $baseSlug : 'category';
        $counter = 1;

        while (
            Category::query()
                ->where('type', $type)
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
