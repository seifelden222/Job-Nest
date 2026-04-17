<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthService
{
    public function __construct(
        private GoogleTokenVerifier $googleTokenVerifier,
        private AuthTokenService $authTokenService,
        private AuthService $authService,
    ) {}

    public function login(array $validated, Request $request): array
    {
        $googleUser = $this->googleTokenVerifier->verify($validated['id_token']);

        return DB::transaction(function () use ($validated, $request, $googleUser): array {
            $userByGoogleId = User::query()
                ->where('google_id', $googleUser['google_id'])
                ->first();
            $userByEmail = User::query()
                ->where('email', $googleUser['email'])
                ->first();

            if (
                $userByGoogleId instanceof User
                && $userByEmail instanceof User
                && $userByGoogleId->isNot($userByEmail)
            ) {
                throw ValidationException::withMessages([
                    'id_token' => ['This Google account is already linked to another user.'],
                ]);
            }

            $user = $userByGoogleId ?? $userByEmail;

            $isNewUser = false;

            if (! $user instanceof User) {
                $user = $this->createUserFromGooglePayload($googleUser, $validated);
                $isNewUser = true;
            } elseif ($user->google_id !== null && ! hash_equals($user->google_id, $googleUser['google_id'])) {
                throw ValidationException::withMessages([
                    'id_token' => ['This Google account is already linked to another user.'],
                ]);
            } elseif ($user->google_id === null) {
                $user->forceFill([
                    'google_id' => $googleUser['google_id'],
                ])->save();
            }

            $tokenData = $this->authTokenService->issueToken(
                $user,
                $request,
                'google-login',
                $validated['device_name'] ?? null,
            );

            return [
                'user' => $this->authService->loadUserProfiles($user->fresh()),
                'is_new_user' => $isNewUser,
                ...$tokenData,
            ];
        });
    }

    /**
     * @param  array{google_id:string,email:string,name:string,picture:?string}  $googleUser
     */
    private function createUserFromGooglePayload(array $googleUser, array $validated): User
    {
        $accountType = $validated['account_type'] ?? 'person';
        $name = trim((string) ($googleUser['name'] ?? '')) ?: Str::before($googleUser['email'], '@');

        $user = User::create([
            'name' => $name,
            'email' => $googleUser['email'],
            'google_id' => $googleUser['google_id'],
            'phone' => null,
            'password' => Str::password(32),
            'account_type' => $accountType,
            'status' => 'active',
        ]);

        $this->authService->createProfileForUser($user, [
            'company_name' => $validated['company_name'] ?? $name,
        ]);

        return $user;
    }
}
