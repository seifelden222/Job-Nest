<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        );

        return [
            'token' => $newAccessToken->plainTextToken,
            'token_type' => 'Bearer',
            'current_token' => $this->describeToken($newAccessToken->accessToken, $newAccessToken->accessToken->getKey()),
        ];
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

        $token->delete();
    }

    public function revokeAllTokens(User $user): int
    {
        return $user->tokens()->delete();
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
}
