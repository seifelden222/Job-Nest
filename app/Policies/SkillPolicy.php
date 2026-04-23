<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Skill $skill): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function update(User $user, Skill $skill): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
