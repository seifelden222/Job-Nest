<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class ConversationPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, Conversation $conversation): bool
    {
        return $conversation->participants()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function createForApplication(User|Admin $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id
            || ($user->isCompany() && (int) $application->job->company_id === (int) $user->id);
    }
}
