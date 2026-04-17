<?php

namespace App\Services\Auth;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleTokenVerifier
{
    /**
     * @return array{
     *     google_id:string,
     *     email:string,
     *     name:string,
     *     picture:?string
     * }
     */
    public function verify(string $idToken): array
    {
        try {
            $response = Http::acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry(2, 200)
                ->get('https://oauth2.googleapis.com/tokeninfo', [
                    'id_token' => $idToken,
                ]);
        } catch (RequestException|\Throwable) {
            $this->throwInvalidToken();
        }

        if (! $response->successful()) {
            $this->throwInvalidToken();
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            $this->throwInvalidToken();
        }

        $issuer = (string) ($payload['iss'] ?? '');
        $audience = (string) ($payload['aud'] ?? '');
        $subject = (string) ($payload['sub'] ?? '');
        $email = Str::lower(trim((string) ($payload['email'] ?? '')));
        $name = trim((string) ($payload['name'] ?? ''));
        $picture = $payload['picture'] ?? null;
        $expiresAt = (int) ($payload['exp'] ?? 0);

        if ($subject === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->throwInvalidToken();
        }

        if (! in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            $this->throwInvalidToken();
        }

        if ($expiresAt <= now()->timestamp) {
            $this->throwInvalidToken('Google ID token has expired.');
        }

        $configuredClientId = (string) config('services.google.client_id');

        if ($configuredClientId !== '' && ! hash_equals($configuredClientId, $audience)) {
            $this->throwInvalidToken('Google ID token audience is invalid.');
        }

        return [
            'google_id' => $subject,
            'email' => $email,
            'name' => $name !== '' ? $name : Str::before($email, '@'),
            'picture' => is_string($picture) ? $picture : null,
        ];
    }

    private function throwInvalidToken(string $message = 'Invalid Google ID token.'): never
    {
        throw ValidationException::withMessages([
            'id_token' => [$message],
        ]);
    }
}
