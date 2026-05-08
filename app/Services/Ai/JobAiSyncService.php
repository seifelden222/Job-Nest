<?php

namespace App\Services\Ai;

use App\Models\Job;
use Illuminate\Support\Facades\Log;

class JobAiSyncService
{
    public function __construct(
        private readonly JobAiPayloadBuilder $payloadBuilder,
        private readonly ExternalAiClient $externalAiClient,
    ) {}

    public function syncIfEligible(Job $job): void
    {
        if ($job->ai_job_id !== null) {
            return;
        }

        $payload = $this->payloadBuilder->build($job->fresh());
        $missingFields = $this->missingRequiredFields($payload);

        if ($missingFields !== []) {
            Log::warning('Job AI sync skipped because required fields are missing.', [
                'job_id' => $job->id,
                'missing_fields' => $missingFields,
            ]);

            return;
        }

        $response = $this->externalAiClient->syncJob($payload);
        $aiJobId = data_get($response, 'job_id') ?? data_get($response, 'id');

        if (! is_numeric($aiJobId)) {
            Log::warning('Job AI sync response did not contain job_id.', [
                'job_id' => $job->id,
                'response' => $response,
            ]);

            return;
        }

        $job->forceFill([
            'ai_job_id' => (int) $aiJobId,
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
