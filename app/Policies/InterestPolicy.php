<?php

namespace App\Policies;

use App\Models\Interest;
use App\Models\User;

class InterestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Interest $interest): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function update(User $user, Interest $interest): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function delete(User $user, Interest $interest): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
