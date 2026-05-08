<?php

namespace App\Observers;

use App\Models\PersonProfile;
use App\Services\Ai\UserAiSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PersonProfileObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly UserAiSyncService $userAiSyncService) {}

    public function created(PersonProfile $personProfile): void
    {
        $this->userAiSyncService->syncIfEligible($personProfile->user()->firstOrFail());
    }

    public function updated(PersonProfile $personProfile): void
    {
        $this->userAiSyncService->syncIfEligible($personProfile->user()->firstOrFail());
    }
}
