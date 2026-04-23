<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function create(User $user): bool
    {
        return $user->isCompany() && $user->isActive();
    }

    public function update(User $user, Job $job): bool
    {
        return $user->isCompany()
            && $user->isActive()
            && (int) $user->id === (int) $job->company_id;
    }

    public function delete(User $user, Job $job): bool
    {
        return $this->update($user, $job);
    }
}
