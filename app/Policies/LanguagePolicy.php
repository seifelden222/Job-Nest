<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Language;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class LanguagePolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, Language $language): bool
    {
        return $user->isActive();
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function update(User|Admin $user, Language $language): bool
    {
        return $user->isActive() && $user->isAdmin();
    }

    public function delete(User|Admin $user, Language $language): bool
    {
        return $user->isActive() && $user->isAdmin();
    }
}
