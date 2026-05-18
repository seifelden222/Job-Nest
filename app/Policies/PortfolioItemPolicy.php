<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\PortfolioItem;
use App\Models\User;
use App\Policies\Concerns\HandlesAdminAccess;

class PortfolioItemPolicy
{
    use HandlesAdminAccess;

    public function viewAny(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function view(User|Admin $user, PortfolioItem $portfolioItem): bool
    {
        if ($user instanceof Admin) {
            return $user->isActive();
        }

        // allow owner or public
        return (int) $user->id === (int) $portfolioItem->user_id || $portfolioItem->is_public === true;
    }

    public function create(User|Admin $user): bool
    {
        return $user->isActive();
    }

    public function update(User|Admin $user, PortfolioItem $portfolioItem): bool
    {
        return $user->isActive() && (int) $user->id === (int) $portfolioItem->user_id;
    }

    public function delete(User|Admin $user, PortfolioItem $portfolioItem): bool
    {
        return $this->update($user, $portfolioItem);
    }
}
