<?php

namespace App\Http\Controllers\Api\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Ai\IndexAiCourseRequest;
use App\Http\Requests\Api\Ai\IndexAiJobRequest;
use App\Http\Requests\Api\Ai\SearchAiUserRequest;
use App\Models\Job;
use App\Models\User;
use App\Services\Ai\AiGatewayService;
use Illuminate\Http\JsonResponse;

class GatewayController extends Controller
{
    public function __construct(private readonly AiGatewayService $aiGatewayService) {}

    public function searchUsers(SearchAiUserRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'AI users fetched successfully.',
            'data' => $this->aiGatewayService->searchUsers($request->validated()),
        ]);
    }

    public function showUser(User $user): JsonResponse
    {
        return response()->json([
            'message' => 'AI user fetched successfully.',
            'data' => $this->aiGatewayService->showUser($user),
        ]);
    }

    public function jobs(IndexAiJobRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'AI jobs fetched successfully.',
            'data' => $this->aiGatewayService->listJobs($request->validated()),
        ]);
    }

    public function jobScore(Job $job): JsonResponse
    {
        return response()->json([
            'message' => 'AI job score fetched successfully.',
            'data' => $this->aiGatewayService->jobScore($job),
        ]);
    }

    public function courses(IndexAiCourseRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'AI courses fetched successfully.',
            'data' => $this->aiGatewayService->listCourses($request->validated()),
        ]);
    }
}
