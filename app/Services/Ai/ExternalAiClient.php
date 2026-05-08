<?php

namespace App\Services\Ai;

use App\Exceptions\ExternalAiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExternalAiClient
{
    /**
     * @return array<string, mixed>
     */
    public function health(): array
    {
        return $this->get(
            (string) config('ai.endpoints.health'),
            [],
            'AI health information is unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function recommend(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.recommend'),
            $payload,
            'AI recommendations are unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function recommendRealtime(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.recommend_realtime'),
            $payload,
            'AI realtime recommendations are unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function searchUsers(array $payload): array
    {
        return $this->get(
            (string) config('ai.endpoints.user_search'),
            $payload,
            'AI user search is unavailable right now.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function showUser(int $userId): array
    {
        return $this->get(
            sprintf((string) config('ai.endpoints.user_show'), $userId),
            [],
            'AI user details are unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function syncUser(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.users_new'),
            $payload,
            'User sync to the AI service failed.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function listJobs(array $payload): array
    {
        return $this->get(
            (string) config('ai.endpoints.jobs'),
            $payload,
            'AI job data is unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function syncJob(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.jobs_new'),
            $payload,
            'Job sync to the AI service failed.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jobScore(int $jobId): array
    {
        return $this->get(
            sprintf((string) config('ai.endpoints.job_score'), $jobId),
            [],
            'AI job score data is unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function chat(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.chat'),
            $payload,
            'Unable to generate a chatbot reply right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function listCourses(array $payload): array
    {
        return $this->get(
            (string) config('ai.endpoints.courses'),
            $payload,
            'AI course data is unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function recommendCourses(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.courses_recommend'),
            $payload,
            'AI course recommendations are unavailable right now.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function syncCourse(array $payload): array
    {
        return $this->post(
            (string) config('ai.endpoints.courses_new'),
            $payload,
            'Course sync to the AI service failed.',
        );
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query, string $failureMessage): array
    {
        try {
            $response = $this->request()
                ->get($path, $query)
                ->throw();
        } catch (ExternalAiException $exception) {
            throw $exception;
        } catch (ConnectionException $exception) {
            throw new ExternalAiException($failureMessage, 504, [
                'endpoint' => $path,
                'reason' => 'connection_timeout',
            ]);
        } catch (RequestException $exception) {
            throw new ExternalAiException($failureMessage, 502, [
                'endpoint' => $path,
                'reason' => 'upstream_http_error',
                'upstream_status' => $exception->response?->status(),
            ]);
        } catch (Throwable $exception) {
            throw new ExternalAiException($failureMessage, 502, [
                'endpoint' => $path,
                'reason' => 'unexpected_ai_client_error',
                'exception' => $exception::class,
            ]);
        }

        return $this->decodeResponse($response->json(), $failureMessage, $path);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload, string $failureMessage): array
    {
        try {
            $response = $this->request()
                ->post($path, $payload)
                ->throw();
        } catch (ExternalAiException $exception) {
            throw $exception;
        } catch (ConnectionException $exception) {
            throw new ExternalAiException($failureMessage, 504, [
                'endpoint' => $path,
                'reason' => 'connection_timeout',
            ]);
        } catch (RequestException $exception) {
            throw new ExternalAiException($failureMessage, 502, [
                'endpoint' => $path,
                'reason' => 'upstream_http_error',
                'upstream_status' => $exception->response?->status(),
            ]);
        } catch (Throwable $exception) {
            throw new ExternalAiException($failureMessage, 502, [
                'endpoint' => $path,
                'reason' => 'unexpected_ai_client_error',
                'exception' => $exception::class,
            ]);
        }

        return $this->decodeResponse($response->json(), $failureMessage, $path);
    }

    private function request(): PendingRequest
    {
        $baseUrl = (string) config('ai.base_url', '');

        if ($baseUrl === '') {
            throw new ExternalAiException('The AI service is not configured.', 503, [
                'reason' => 'missing_base_url',
            ]);
        }

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->connectTimeout((int) config('ai.connect_timeout'))
            ->timeout((int) config('ai.timeout'))
            ->retry(
                (int) config('ai.retry_attempts'),
                (int) config('ai.retry_sleep'),
                throw: false,
            );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(mixed $payload, string $failureMessage, string $path): array
    {
        if (! is_array($payload)) {
            throw new ExternalAiException($failureMessage, 502, [
                'endpoint' => $path,
                'reason' => 'invalid_response_payload',
            ]);
        }

        return $payload;
    }
}
