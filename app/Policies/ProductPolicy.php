<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER], true);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->create($user);
    }

    // Master data only — ADMIN owns deletion (least privilege; PRODUCTION_MANAGER has C/R/U only).
    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
