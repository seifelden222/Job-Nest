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
        return true;
    }

    public function view(User|Admin $user, PortfolioItem $portfolioItem): bool
    {
        return (int) $portfolioItem->user_id === (int) $user->id;
    }

    public function create(User|Admin $user): bool
    {
        return $user->isPerson() && $user->isActive();
    }

    public function update(User|Admin $user, PortfolioItem $portfolioItem): bool
    {
        return (int) $portfolioItem->user_id === (int) $user->id;
    }

    public function delete(User|Admin $user, PortfolioItem $portfolioItem): bool
    {
        return (int) $portfolioItem->user_id === (int) $user->id;
    }
}
