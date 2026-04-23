<?php

namespace App\Policies;

use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use App\Models\User;

class ServiceProposalPolicy
{
    public function create(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->isActive()
            && (int) $serviceRequest->user_id !== (int) $user->id;
    }

    public function view(User $user, ServiceProposal $serviceProposal): bool
    {
        return (int) $serviceProposal->serviceRequest->user_id === (int) $user->id
            || (int) $serviceProposal->user_id === (int) $user->id;
    }

    public function update(User $user, ServiceProposal $serviceProposal): bool
    {
        return $this->view($user, $serviceProposal);
    }
}
