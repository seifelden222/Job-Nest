<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Job;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class JobPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, Job $job): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isCompany() && $user->isActive();
    }

    public function update(User|Admin $user, Job $job): bool
    {
        return $user->isCompany()
            && $user->isActive()
            && (int) $user->id === (int) $job->company_id;
    }

    public function delete(User|Admin $user, Job $job): bool
    {
        return $this->update($user, $job);
    }
}
