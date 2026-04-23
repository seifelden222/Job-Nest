<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    public function update(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isActive()
            && (int) $serviceRequest->user_id === (int) $user->id;
    }

    public function delete(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->update($user, $serviceRequest);
    }

    public function viewProposals(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->update($user, $serviceRequest);
    }
}
