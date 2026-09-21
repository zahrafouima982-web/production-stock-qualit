<?php

namespace App\Policies;

use App\Models\Component;
use App\Models\Role;
use App\Models\User;

/**
 * Unlike StockMovement/StockAlert, Components are shared master data —
 * PRODUCTION_MANAGER can read (needs to know what's available) but never
 * writes here. QUALITY_CONTROLLER has no reason to see stock at all.
 */
class ComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::STOCK_MANAGER], true);
    }

    public function view(User $user, Component $component): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::STOCK_MANAGER], true);
    }

    public function update(User $user, Component $component): bool
    {
        return $this->create($user);
    }

    // Unlike Product/ProductionLine (ADMIN-only delete), the permission
    // matrix grants STOCK_MANAGER full CRUD on Components — this department
    // is self-sufficient for its own master data.
    public function delete(User $user, Component $component): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::STOCK_MANAGER], true);
    }
}
