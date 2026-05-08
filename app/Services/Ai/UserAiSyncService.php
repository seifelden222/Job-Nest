<?php

namespace App\Services\Ai;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserAiSyncService
{
    public function __construct(
        private readonly UserAiPayloadBuilder $payloadBuilder,
        private readonly ExternalAiClient $externalAiClient,
    ) {}

    public function syncIfEligible(User $user): void
    {
        if (! $user->isPerson() || $user->ai_user_id !== null) {
            return;
        }

        $payload = $this->payloadBuilder->build($user->fresh());
        $missingFields = $this->missingRequiredFields($payload);

        if ($missingFields !== []) {
            Log::warning('User AI sync skipped because required fields are missing.', [
                'user_id' => $user->id,
                'missing_fields' => $missingFields,
            ]);

            return;
        }

        $response = $this->externalAiClient->syncUser($payload);
        $aiUserId = data_get($response, 'user_id') ?? data_get($response, 'id');

        if (! is_numeric($aiUserId)) {
            Log::warning('User AI sync response did not contain user_id.', [
                'user_id' => $user->id,
                'response' => $response,
            ]);

            return;
        }

        $user->forceFill([
            'ai_user_id' => (int) $aiUserId,
        ])->saveQuietly();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function missingRequiredFields(array $payload): array
    {
        return collect($this->payloadBuilder->requiredFields())
            ->filter(fn (string $field) => blank($payload[$field] ?? null))
            ->values()
            ->all();
    }
}
