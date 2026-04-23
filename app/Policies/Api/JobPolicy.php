<?php

namespace App\Policies\Api;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function index(User $user): bool
    {
        if ($user->account_type == 'company' && $user->status == 'active') {
            return true;
        } else {
            return false;
        }
    }

    public function show(User $user)
    {
        if ($user->account_type == 'company' && $user->status == 'active') {
            return true;
        } else {
            return false;
        }
    }

    public function update(User $user, Job $job)
    {
        if ($user->account_type == 'company' && $user->status == 'active' && $user->id === $job->company_id) {
            return true;
        } else {
            return false;
        }
    }

    public function destroy(User $user, Job $job)
    {

        if ($user->account_type == 'company' && $user->status == 'active' && $user->id === $job->company_id) {
            return true;
        } else {
            return false;
        }
    }

    public function store(User $user)
    {

        if ($user->account_type == 'company' && $user->status == 'active') {

            return true;
        } else {
            return false;
        }
    }
}
