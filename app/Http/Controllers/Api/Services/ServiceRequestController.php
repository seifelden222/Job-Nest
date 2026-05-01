<?php

namespace App\Http\Controllers\Api\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Services\StoreServiceRequestRequest;
use App\Http\Requests\Api\Services\UpdateServiceRequestRequest;
use App\Models\ServiceRequest;
use App\Services\Translation\ContentTranslationService;
use App\Support\TranslatableJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ServiceRequest::query()
            ->with(['owner:id,name,account_type', 'category:id,name,slug,type', 'skills:id,name'])
            ->open();

        if ($request->filled('q')) {
            $term = (string) $request->query('q');
            $query->where(function ($inner) use ($term) {
                TranslatableJson::whereLike($inner, 'title', $term);
                TranslatableJson::orWhereLike($inner, 'description', $term);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->query('category_id'));
        }

        if ($request->filled('skill_id')) {
            $skillId = (int) $request->query('skill_id');
            $query->whereHas('skills', fn ($skills) => $skills->where('skills.id', $skillId));
        }

        if ($request->filled('delivery_mode')) {
            $query->where('delivery_mode', (string) $request->query('delivery_mode'));
        }

        $serviceRequests = $query->latest()->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'Service requests fetched successfully.',
            'data' => $serviceRequests,
        ]);
    }

    public function myRequests(Request $request): JsonResponse
    {
        $serviceRequests = ServiceRequest::query()
            ->with(['category:id,name,slug,type', 'skills:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'message' => 'User service requests fetched successfully.',
            'data' => $serviceRequests,
        ]);
    }

    public function store(StoreServiceRequestRequest $request, ContentTranslationService $translationService): JsonResponse
    {
        $this->authorize('create', ServiceRequest::class);

        $payload = $request->validated();
        $skillIds = $payload['skill_ids'] ?? [];
        unset($payload['skill_ids']);
        $payload = $translationService->translatePayload(
            $payload,
            ['title', 'description'],
            (string) $request->validated('source_language'),
        );

        $payload['user_id'] = $request->user()->id;
        $payload['status'] = $payload['status'] ?? 'open';

        $serviceRequest = ServiceRequest::create($payload);

        if (! empty($skillIds)) {
            $serviceRequest->skills()->sync($skillIds);
        }

        return response()->json([
            'message' => 'Service request created successfully.',
            'data' => $serviceRequest->load(['owner:id,name,account_type', 'category:id,name,slug,type', 'skills:id,name']),
        ], 201);
    }

    public function show(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $isOwner = (int) $serviceRequest->user_id === (int) $request->user()?->id;

        if ($serviceRequest->status !== 'open' && ! $isOwner) {
            return response()->json([
                'message' => 'Service request not found.',
            ], 404);
        }

        $serviceRequest->load(['owner:id,name,account_type', 'category:id,name,slug,type', 'skills:id,name']);

        return response()->json([
            'message' => 'Service request fetched successfully.',
            'data' => $serviceRequest,
        ]);
    }

    public function update(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest, ContentTranslationService $translationService): JsonResponse
    {
        $this->authorize('update', $serviceRequest);

        $payload = $request->validated();
        $skillIds = $payload['skill_ids'] ?? null;
        unset($payload['skill_ids']);

        if ($request->filled('source_language')) {
            $payload = $translationService->translatePayload(
                $payload,
                ['title', 'description'],
                (string) $request->validated('source_language'),
            );
        }

        $serviceRequest->update($payload);

        if (is_array($skillIds)) {
            $serviceRequest->skills()->sync($skillIds);
        }

        return response()->json([
            'message' => 'Service request updated successfully.',
            'data' => $serviceRequest->fresh(['owner:id,name,account_type', 'category:id,name,slug,type', 'skills:id,name']),
        ]);
    }

    public function destroy(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $this->authorize('delete', $serviceRequest);

        $serviceRequest->delete();

        return response()->json([
            'message' => 'Service request deleted successfully.',
        ]);
    }
}
