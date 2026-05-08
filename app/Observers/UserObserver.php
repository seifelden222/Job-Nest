<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Ai\UserAiSyncService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(private readonly UserAiSyncService $userAiSyncService) {}

    public function created(User $user): void
    {
        $this->userAiSyncService->syncIfEligible($user);
    }

    public function updated(User $user): void
    {
        $this->userAiSyncService->syncIfEligible($user);
    }
}
