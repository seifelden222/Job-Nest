<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\Auth\GoogleLoginRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\Auth\RegisterCompanyRequest;
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
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

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
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Registration step 1 failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration step 1 failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerCompany(RegisterCompanyRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->registerCompany($request->validated(), $request);

            return $this->authResponse(
                message: 'Registration completed successfully.',
                user: $result['user'],
                status: 201,
                tokenData: $result,
                currentStep: 3,
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Company registration failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Company registration failed.',
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
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Registration step 2 failed.',
                'errors' => $e->errors(),
            ], 422);
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
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Registration step 3 failed.',
                'errors' => $e->errors(),
            ], 422);
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

    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $tokenData = $this->authTokenService->refreshToken(
            plainRefreshToken: $request->validated('refresh_token'),
            request: $request,
            requestedDeviceName: $request->validated('device_name'),
        );

        $accessToken = PersonalAccessToken::findToken($tokenData['token']);

        if (! $accessToken instanceof PersonalAccessToken || ! $accessToken->tokenable instanceof User) {
            return response()->json([
                'message' => 'Failed to refresh token.',
            ], 500);
        }

        return $this->authResponse(
            message: 'Token refreshed successfully.',
            user: $this->authService->me($accessToken->tokenable),
            tokenData: $tokenData,
        );
    }

    public function sendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        $this->authService->sendEmailVerification($user);

        return response()->json([
            'message' => 'Verification email sent successfully.',
        ]);
    }

    public function resendEmailVerification(Request $request): JsonResponse
    {
        return $this->sendEmailVerification($request);
    }

    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        if (! URL::hasValidSignature($request)) {
            return response()->json([
                'message' => 'The verification link is invalid or has expired.',
            ], 422);
        }

        $user = User::query()->find($id);

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'The verification link is invalid.',
            ], 422);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'message' => 'The verification link is invalid.',
            ], 422);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully.',
            'email_verified' => true,
            'email_verified_at' => $user->fresh()->email_verified_at?->toIso8601String(),
        ]);
    }

    public function verificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'message' => 'Verification status fetched successfully.',
            'email_verified' => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
        ]);
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
        $revocationData = $this->authService->logoutAll($request->user());

        return response()->json([
            'message' => 'Logged out from all devices successfully.',
            'revoked_tokens_count' => $revocationData['revoked_access_tokens_count'],
            'revoked_refresh_tokens_count' => $revocationData['revoked_refresh_tokens_count'],
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
            $payload['access_token'] = $tokenData['access_token'] ?? $tokenData['token'];
            $payload['refresh_token'] = $tokenData['refresh_token'] ?? null;
            $payload['token_type'] = $tokenData['token_type'];
            $payload['expires_at'] = $tokenData['expires_at'] ?? null;
            $payload['access_token_expires_at'] = $tokenData['access_token_expires_at'] ?? null;
            $payload['refresh_token_expires_at'] = $tokenData['refresh_token_expires_at'] ?? null;
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
