<?php

namespace App\Policies;

use App\Models\ProductionOrder;
use App\Models\Role;
use App\Models\User;

/**
 * ADMIN deliberately has read/supervise access only here — order creation and
 * updates stay with PRODUCTION_MANAGER (least privilege, per the architecture
 * doc: ADMIN does not perform other departments' operational actions).
 * No delete() method — production_orders are never hard-deleted; see cancel().
 */
class ProductionOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, ProductionOrder $order): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isProductionManager();
    }

    public function update(User $user, ProductionOrder $order): bool
    {
        return $user->isProductionManager() && $order->isCancellable();
    }

    public function cancel(User $user, ProductionOrder $order): bool
    {
        return $user->isProductionManager() && $order->isCancellable();
    }
}
