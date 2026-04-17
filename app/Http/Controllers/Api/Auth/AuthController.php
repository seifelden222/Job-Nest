<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\GoogleLoginRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterStepOneRequest;
use App\Http\Requests\Api\Auth\RegisterStepThreeRequest;
use App\Http\Requests\Api\Auth\RegisterStepTwoRequest;
use App\Http\Requests\Api\Auth\ResendResetOtpRequest;
use App\Http\Requests\Api\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\Auth\VerifyResetOtpRequest;
use App\Http\Resources\Auth\AuthSessionResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\AuthTokenService;
use App\Services\Auth\ForgotPasswordService;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private AuthTokenService $authTokenService,
        private ForgotPasswordService $forgotPasswordService,
        private GoogleAuthService $googleAuthService,
    ) {}

    public function registerStepOne(RegisterStepOneRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->registerStepOne($request->validated(), $request);

            return $this->authResponse(
                message: 'Step 1 completed successfully.',
                user: $result['user'],
                status: 201,
                tokenData: $result,
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
        $result = $this->authService->login($request->validated(), $request);

        return $this->authResponse(
            message: 'Logged in successfully.',
            user: $result['user'],
            tokenData: $result,
        );
    }

    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $result = $this->googleAuthService->login($request->validated(), $request);

        return $this->authResponse(
            message: 'Logged in with Google successfully.',
            user: $result['user'],
            tokenData: $result,
            extra: [
                'is_new_user' => $result['is_new_user'],
            ],
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

    public function logoutAll(Request $request): JsonResponse
    {
        $revokedTokensCount = $this->authTokenService->revokeAllTokens($request->user());

        return response()->json([
            'message' => 'Logged out from all devices successfully.',
            'revoked_tokens_count' => $revokedTokensCount,
        ]);
    }

    public function sessions(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();
        $sessions = $this->authTokenService->listTokens($request->user(), $currentToken);

        return response()->json([
            'message' => 'Active sessions fetched successfully.',
            'current_token_id' => $currentToken ? $this->authTokenService->toPublicId($currentToken) : null,
            'sessions' => AuthSessionResource::collection($sessions)->resolve(),
        ]);
    }

    public function revokeSession(Request $request, string $sessionId): JsonResponse
    {
        $this->authTokenService->revokeTokenByPublicId($request->user(), $sessionId);

        return response()->json([
            'message' => 'Session revoked successfully.',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->forgotPasswordService->sendResetOtp($request->validated());

        return response()->json([
            'message' => 'OTP sent successfully.',
        ]);
    }

    public function resendResetOtp(ResendResetOtpRequest $request): JsonResponse
    {
        $this->forgotPasswordService->resendResetOtp($request->validated());

        return response()->json([
            'message' => 'OTP resent successfully.',
        ]);
    }

    public function verifyResetOtp(VerifyResetOtpRequest $request): JsonResponse
    {
        $this->forgotPasswordService->verifyResetOtp($request->validated());

        return response()->json([
            'message' => 'OTP verified successfully.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->forgotPasswordService->resetPassword($request->validated());

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {

        try {
            $this->authService->changePassword($request->user(), $request->validated());

            return response()->json([
                'message' => 'Password changed successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Password change failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function authResponse(
        string $message,
        User $user,
        int $status = 200,
        ?array $tokenData = null,
        ?int $currentStep = null,
        array $extra = [],
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'user' => new UserResource($user),
        ];

        if ($tokenData !== null) {
            $payload['token'] = $tokenData['token'];
            $payload['token_type'] = $tokenData['token_type'];
            $payload['current_token_id'] = $tokenData['current_token']['id'];
            $payload['current_token'] = $tokenData['current_token'];
        }

        if ($currentStep !== null) {
            $payload['current_step'] = $currentStep;
        }

        return response()->json([
            ...$payload,
            ...$extra,
        ], $status);
    }
}
