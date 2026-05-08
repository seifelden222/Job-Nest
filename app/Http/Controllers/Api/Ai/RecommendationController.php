<?php

namespace App\Http\Controllers\Api\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ai\StoreAiRealtimeRecommendationRequest;
use App\Http\Requests\Api\Ai\StoreAiRecommendationRequest;
use App\Services\Ai\RecommendationService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendationService) {}

    public function recommend(StoreAiRecommendationRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'AI recommendations fetched successfully.',
            'data' => $this->recommendationService->recommend($request->user(), $request->validated()),
        ]);
    }

    public function realtime(StoreAiRealtimeRecommendationRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'AI realtime recommendations fetched successfully.',
            'data' => $this->recommendationService->recommendRealtime($request->user(), $request->validated()),
        ]);
    }

    public function courses(StoreAiRecommendationRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'AI course recommendations fetched successfully.',
            'data' => $this->recommendationService->recommendCourses($request->user(), $request->validated()),
        ]);
    }
}
