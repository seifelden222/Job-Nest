<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class ServiceProposalPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user, ?ServiceRequest $serviceRequest = null): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if (! $serviceRequest instanceof ServiceRequest) {
            return true;
        }

        return (int) $serviceRequest->user_id !== (int) $user->id;
    }

    public function view(User|Admin $user, ServiceProposal $serviceProposal): bool
    {
        return (int) $serviceProposal->serviceRequest->user_id === (int) $user->id
            || (int) $serviceProposal->user_id === (int) $user->id;
    }

    public function update(User|Admin $user, ServiceProposal $serviceProposal): bool
    {
        return $this->view($user, $serviceProposal);
    }
}
