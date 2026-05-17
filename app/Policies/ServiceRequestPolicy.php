<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class ServiceRequestPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function update(User|Admin $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isActive()
            && (int) $serviceRequest->user_id === (int) $user->id;
    }

    public function delete(User|Admin $user, ServiceRequest $serviceRequest): bool
    {
        return $this->update($user, $serviceRequest);
    }

    public function viewProposals(User|Admin $user, ServiceRequest $serviceRequest): bool
    {
        return $this->update($user, $serviceRequest);
    }
}
