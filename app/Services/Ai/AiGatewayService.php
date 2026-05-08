<?php

namespace App\Services\Ai;

use App\Exceptions\ExternalAiException;
use App\Models\Job;
use App\Models\User;

class AiGatewayService
{
    public function __construct(private readonly ExternalAiClient $externalAiClient) {}

    /**
     * @return array<string, mixed>
     */
    public function health(): array
    {
        return $this->externalAiClient->health();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function searchUsers(array $filters): array
    {
        return $this->externalAiClient->searchUsers($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function showUser(User $user): array
    {
        if ($user->ai_user_id === null) {
            throw new ExternalAiException('This user has not been synced to the AI service yet.', 409, [
                'model' => User::class,
                'model_id' => $user->id,
                'reason' => 'missing_ai_user_id',
            ]);
        }

        return $this->externalAiClient->showUser($user->ai_user_id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listJobs(array $filters): array
    {
        return $this->externalAiClient->listJobs($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function jobScore(Job $job): array
    {
        if ($job->ai_job_id === null) {
            throw new ExternalAiException('This job has not been synced to the AI service yet.', 409, [
                'model' => Job::class,
                'model_id' => $job->id,
                'reason' => 'missing_ai_job_id',
            ]);
        }

        return $this->externalAiClient->jobScore($job->ai_job_id);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listCourses(array $filters): array
    {
        return $this->externalAiClient->listCourses($filters);
    }
}
