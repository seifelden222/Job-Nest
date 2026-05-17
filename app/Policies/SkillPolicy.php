<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Skill;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class SkillPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, Skill $skill): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function update(User|Admin $user, Skill $skill): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function delete(User|Admin $user, Skill $skill): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
