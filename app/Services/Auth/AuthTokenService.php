<?php

namespace App\Services\Auth;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTokenService
{
    public function issueToken(
        User $user,
        Request $request,
        string $context,
        ?string $requestedDeviceName = null,
    ): array {
        $newAccessToken = $user->createToken(
            $this->buildTokenName($request, $context, $requestedDeviceName),
            ['*'],
            now()->addMinutes($this->accessTokenTtlMinutes()),
        );

        $refreshTokenData = $this->createRefreshToken(
            user: $user,
            accessToken: $newAccessToken->accessToken,
            request: $request,
            requestedDeviceName: $requestedDeviceName,
        );

        return [
            'token' => $newAccessToken->plainTextToken,
            'access_token' => $newAccessToken->plainTextToken,
            'refresh_token' => $refreshTokenData['plain_text_token'],
            'token_type' => 'Bearer',
            'expires_at' => $newAccessToken->accessToken->expires_at?->toIso8601String(),
            'access_token_expires_at' => $newAccessToken->accessToken->expires_at?->toIso8601String(),
            'refresh_token_expires_at' => $refreshTokenData['expires_at'],
            'current_token' => $this->describeToken($newAccessToken->accessToken, $newAccessToken->accessToken->getKey()),
        ];
    }

    public function refreshToken(string $plainRefreshToken, Request $request, ?string $requestedDeviceName = null): array
    {
        $tokenHash = hash('sha256', $plainRefreshToken);

        $refreshToken = RefreshToken::query()
            ->where('token_hash', $tokenHash)
            ->first();

        if (! $refreshToken instanceof RefreshToken) {
            throw ValidationException::withMessages([
                'refresh_token' => ['The refresh token is invalid.'],
            ]);
        }

        if ($refreshToken->revoked_at !== null) {
            if ($refreshToken->replaced_by_token_id !== null) {
                $this->revokeRefreshTokenFamily($refreshToken->family_id);
            }

            throw ValidationException::withMessages([
                'refresh_token' => ['The refresh token is no longer active.'],
            ]);
        }

        if ($refreshToken->expires_at->isPast()) {
            $refreshToken->forceFill(['revoked_at' => now()])->save();

            throw ValidationException::withMessages([
                'refresh_token' => ['The refresh token has expired.'],
            ]);
        }

        $user = $refreshToken->user;

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'refresh_token' => ['The refresh token is invalid.'],
            ]);
        }

        return DB::transaction(function () use ($refreshToken, $user, $request, $requestedDeviceName): array {
            $refreshToken->forceFill([
                'revoked_at' => now(),
                'last_used_at' => now(),
            ])->save();

            if ($refreshToken->accessToken instanceof PersonalAccessToken) {
                $refreshToken->accessToken->delete();
            }

            $newAccessToken = $user->createToken(
                $this->buildTokenName($request, 'refresh', $requestedDeviceName),
                ['*'],
                now()->addMinutes($this->accessTokenTtlMinutes()),
            );

            $newRefreshTokenData = $this->createRefreshToken(
                user: $user,
                accessToken: $newAccessToken->accessToken,
                request: $request,
                requestedDeviceName: $requestedDeviceName,
                familyId: $refreshToken->family_id,
            );

            $refreshToken->forceFill([
                'replaced_by_token_id' => $newRefreshTokenData['model']->id,
            ])->save();

            return [
                'token' => $newAccessToken->plainTextToken,
                'access_token' => $newAccessToken->plainTextToken,
                'refresh_token' => $newRefreshTokenData['plain_text_token'],
                'token_type' => 'Bearer',
                'expires_at' => $newAccessToken->accessToken->expires_at?->toIso8601String(),
                'access_token_expires_at' => $newAccessToken->accessToken->expires_at?->toIso8601String(),
                'refresh_token_expires_at' => $newRefreshTokenData['expires_at'],
                'current_token' => $this->describeToken($newAccessToken->accessToken, $newAccessToken->accessToken->getKey()),
            ];
        });
    }

    public function listTokens(User $user, ?PersonalAccessToken $currentAccessToken = null): Collection
    {
        $currentTokenId = $currentAccessToken?->getKey();

        return $user->tokens()
            ->latest('id')
            ->get()
            ->map(fn (PersonalAccessToken $token): array => $this->describeToken($token, $currentTokenId));
    }

    public function revokeTokenByPublicId(User $user, string $publicId): void
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $publicId)) {
            throw ValidationException::withMessages([
                'session_id' => ['The selected session is invalid.'],
            ]);
        }

        $token = $user->tokens()
            ->get()
            ->first(fn (PersonalAccessToken $token): bool => hash_equals($this->toPublicId($token), $publicId));

        if (! $token instanceof PersonalAccessToken) {
            throw ValidationException::withMessages([
                'session_id' => ['The selected session could not be found.'],
            ]);
        }

        $this->revokeRefreshTokensByAccessTokenId((int) $token->getKey());

        $token->delete();
    }

    public function revokeAllTokens(User $user): int
    {
        return $user->tokens()->delete();
    }

    public function revokeRefreshTokensByAccessTokenId(int $accessTokenId): int
    {
        return RefreshToken::query()
            ->where('access_token_id', $accessTokenId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }

    public function revokeAllRefreshTokens(User $user): int
    {
        return RefreshToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }

    public function toPublicId(PersonalAccessToken $token): string
    {
        return hash_hmac('sha256', (string) $token->getKey(), (string) config('app.key'));
    }

    /**
     * @return array{id:string,name:string,current:bool,abilities:array<int, string>,last_used_at:?string,created_at:?string,expires_at:?string}
     */
    public function describeToken(PersonalAccessToken $token, ?int $currentTokenId = null): array
    {
        $abilities = $token->abilities ?? [];

        return [
            'id' => $this->toPublicId($token),
            'name' => (string) $token->name,
            'current' => $currentTokenId !== null && $token->getKey() === $currentTokenId,
            'abilities' => is_array($abilities) ? array_values($abilities) : [],
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'created_at' => $token->created_at?->toIso8601String(),
            'expires_at' => $token->expires_at?->toIso8601String(),
        ];
    }

    private function buildTokenName(Request $request, string $context, ?string $requestedDeviceName = null): string
    {
        $deviceName = $this->resolveDeviceName($request, $requestedDeviceName);

        return Str::of($context)
            ->replace('_', '-')
            ->append(':')
            ->append($deviceName)
            ->limit(120, '')
            ->value();
    }

    private function resolveDeviceName(Request $request, ?string $requestedDeviceName = null): string
    {
        $requestedDeviceName = Str::of((string) $requestedDeviceName)->trim()->limit(80, '')->value();

        if ($requestedDeviceName !== '') {
            return $requestedDeviceName;
        }

        $userAgent = Str::lower((string) $request->userAgent());

        return match (true) {
            Str::contains($userAgent, 'iphone') => 'iphone',
            Str::contains($userAgent, 'ipad') => 'ipad',
            Str::contains($userAgent, 'android') => 'android',
            Str::contains($userAgent, 'macintosh') => 'mac',
            Str::contains($userAgent, 'windows') => 'windows',
            Str::contains($userAgent, 'linux') => 'linux',
            $userAgent !== '' => 'browser',
            default => 'unknown-device',
        };
    }

    private function accessTokenTtlMinutes(): int
    {
        return max(1, (int) config('auth_tokens.access_token_ttl_minutes', 15));
    }

    private function refreshTokenTtlDays(): int
    {
        return max(1, (int) config('auth_tokens.refresh_token_ttl_days', 30));
    }

    private function revokeRefreshTokenFamily(string $familyId): void
    {
        RefreshToken::query()
            ->where('family_id', $familyId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
            ]);
    }

    /**
     * @return array{model:RefreshToken,plain_text_token:string,expires_at:string}
     */
    private function createRefreshToken(
        User $user,
        PersonalAccessToken $accessToken,
        Request $request,
        ?string $requestedDeviceName = null,
        ?string $familyId = null,
    ): array {
        do {
            $plainTextToken = bin2hex(random_bytes(64));
            $tokenHash = hash('sha256', $plainTextToken);
            $exists = RefreshToken::query()->where('token_hash', $tokenHash)->exists();
        } while ($exists);

        $refreshToken = RefreshToken::query()->create([
            'user_id' => $user->id,
            'access_token_id' => $accessToken->getKey(),
            'family_id' => $familyId ?? (string) Str::uuid(),
            'name' => $this->resolveDeviceName($request, $requestedDeviceName),
            'token_hash' => $tokenHash,
            'ip_address' => (string) $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'expires_at' => now()->addDays($this->refreshTokenTtlDays()),
        ]);

        return [
            'model' => $refreshToken,
            'plain_text_token' => $plainTextToken,
            'expires_at' => $refreshToken->expires_at?->toIso8601String() ?? now()->addDays($this->refreshTokenTtlDays())->toIso8601String(),
        ];
    }
}
