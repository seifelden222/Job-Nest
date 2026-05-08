<?php

namespace App\Observers;

use App\Models\Job;
use App\Services\Ai\JobAiSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class JobObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly JobAiSyncService $jobAiSyncService) {}

    public function created(Job $job): void
    {
        app()->terminating(function () use ($job): void {
            $freshJob = Job::query()->find($job->id);

            if ($freshJob !== null) {
                $this->jobAiSyncService->syncIfEligible($freshJob);
            }
        });
    }

    public function updated(Job $job): void
    {
        app()->terminating(function () use ($job): void {
            $freshJob = Job::query()->find($job->id);

            if ($freshJob !== null) {
                $this->jobAiSyncService->syncIfEligible($freshJob);
            }
        });
    }
}
