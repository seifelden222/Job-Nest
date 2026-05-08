<?php

namespace App\Services\Ai;

use App\Models\Course;
use Illuminate\Support\Facades\Log;

class CourseAiSyncService
{
    public function __construct(
        private readonly CourseAiPayloadBuilder $payloadBuilder,
        private readonly ExternalAiClient $externalAiClient,
    ) {}

    public function syncIfEligible(Course $course): void
    {
        if ($course->ai_course_id !== null) {
            return;
        }

        $payload = $this->payloadBuilder->build($course->fresh());
        $missingFields = $this->missingRequiredFields($payload);

        if ($missingFields !== []) {
            Log::warning('Course AI sync skipped because required fields are missing.', [
                'course_id' => $course->id,
                'missing_fields' => $missingFields,
            ]);

            return;
        }

        $response = $this->externalAiClient->syncCourse($payload);
        $aiCourseId = data_get($response, 'course_id') ?? data_get($response, 'id');

        if (! is_numeric($aiCourseId)) {
            Log::warning('Course AI sync response did not contain course_id.', [
                'course_id' => $course->id,
                'response' => $response,
            ]);

            return;
        }

        $course->forceFill([
            'ai_course_id' => (int) $aiCourseId,
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
