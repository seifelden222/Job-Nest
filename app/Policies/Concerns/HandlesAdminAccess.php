<?php

namespace App\Policies\Concerns;

use App\Models\Admin;
use App\Models\User;

trait HandlesAdminAccess
{
    public function before(User|Admin $user, string $ability): ?bool
    {
        if ($user instanceof Admin) {
            return $user->isActive();
        }

        return null;
    }
}
