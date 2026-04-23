<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function createForApplication(User $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id
            || ($user->isCompany() && (int) $application->job->company_id === (int) $user->id);
    }
}
