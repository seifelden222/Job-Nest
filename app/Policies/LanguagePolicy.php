<?php

namespace App\Policies;

use App\Models\Language;
use App\Models\User;

class LanguagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(User $user, Language $language): bool
    {
        return $user->isActive();
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function update(User $user, Language $language): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function delete(User $user, Language $language): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
