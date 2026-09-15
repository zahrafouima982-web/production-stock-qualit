<?php

namespace App\Policies;

use App\Models\ProductionLine;
use App\Models\Role;
use App\Models\User;

class ProductionLinePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, ProductionLine $productionLine): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER], true);
    }

    public function update(User $user, ProductionLine $productionLine): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, ProductionLine $productionLine): bool
    {
        return $user->isAdmin();
    }
}
