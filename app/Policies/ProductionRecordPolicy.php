<?php

namespace App\Policies;

use App\Models\ProductionRecord;
use App\Models\Role;
use App\Models\User;

/**
 * Same read distribution as ProductionOrderPolicy. No delete() — production
 * records are an append-only log; corrections happen via update, not deletion.
 */
class ProductionRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, ProductionRecord $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isProductionManager();
    }

    public function update(User $user, ProductionRecord $record): bool
    {
        return $user->isProductionManager();
    }
}
