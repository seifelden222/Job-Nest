<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class ApplicationPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user, ?Job $job = null): bool
    {
        if ($job instanceof Job) {
            return $user->isCompany()
                && $user->isActive()
                && (int) $user->id === (int) $job->company_id;
        }

        return $user->isCompany() && $user->isActive();
    }

    public function view(User|Admin $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id
            || ($user->isCompany() && (int) $application->job->company_id === (int) $user->id);
    }

    public function create(User|Admin $user, ?Job $job = null): bool
    {
        return $user->isPerson() && $user->isActive();
    }

    public function update(User|Admin $user, Application $application): bool
    {
        return $this->view($user, $application);
    }

    public function delete(User|Admin $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id;
    }
}
