<?php

namespace App\Http\Controllers\Api\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiGatewayService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __construct(private readonly AiGatewayService $aiGatewayService) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'message' => 'AI health fetched successfully.',
            'data' => $this->aiGatewayService->health(),
        ]);
    }
}
