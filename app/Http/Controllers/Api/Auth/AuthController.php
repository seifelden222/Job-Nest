<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterStepOneRequest;
use App\Http\Requests\Api\Auth\RegisterStepThreeRequest;
use App\Http\Requests\Api\Auth\RegisterStepTwoRequest;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function registerStepOne(RegisterStepOneRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->registerStepOne($request->validated());

            return $this->authResponse(
                message: 'Step 1 completed successfully.',
                user: $result['user'],
                status: 201,
                token: $result['token'],
                currentStep: 1,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration step 1 failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerStepTwo(RegisterStepTwoRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->registerStepTwo($request->user(), $request->validated());

            return $this->authResponse(
                message: 'Step 2 completed successfully.',
                user: $user,
                currentStep: 2,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration step 2 failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerStepThree(RegisterStepThreeRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->registerStepThree($request, $request->user(), $request->validated());

            return $this->authResponse(
                message: 'Registration completed successfully.',
                user: $user,
                currentStep: 3,
            );
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration step 3 failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->authResponse(
            message: 'Logged in successfully.',
            user: $result['user'],
            token: $result['token'],
        );
    }

    public function me(Request $request): JsonResponse
    {
        return $this->authResponse(
            message: 'Authenticated user fetched successfully.',
            user: $this->authService->me($request->user()),
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function authResponse(
        string $message,
        User $user,
        int $status = 200,
        ?string $token = null,
        ?int $currentStep = null,
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'user' => new UserResource($user),
        ];

        if ($token !== null) {
            $payload['token'] = $token;
        }

        if ($currentStep !== null) {
            $payload['current_step'] = $currentStep;
        }

        return response()->json($payload, $status);
    }
}
