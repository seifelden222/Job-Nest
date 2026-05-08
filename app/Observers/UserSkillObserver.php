<?php

namespace App\Observers;

use App\Models\UserSkill;
use App\Services\Ai\UserAiSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserSkillObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly UserAiSyncService $userAiSyncService) {}

    public function created(UserSkill $userSkill): void
    {
        $this->userAiSyncService->syncIfEligible($userSkill->user()->firstOrFail());
    }
}
