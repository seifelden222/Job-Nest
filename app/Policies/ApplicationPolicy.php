<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user, Job $job): bool
    {
        return $user->isCompany()
            && $user->isActive()
            && (int) $user->id === (int) $job->company_id;
    }

    public function view(User $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id
            || ($user->isCompany() && (int) $application->job->company_id === (int) $user->id);
    }

    public function create(User $user, Job $job): bool
    {
        return $user->isPerson() && $user->isActive();
    }

    public function update(User $user, Application $application): bool
    {
        return $this->view($user, $application);
    }

    public function delete(User $user, Application $application): bool
    {
        return (int) $application->user_id === (int) $user->id;
    }
}
