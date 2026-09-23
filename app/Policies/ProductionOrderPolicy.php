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

    // Only role is checked here — same lesson as CorrectiveActionPolicy::validate()
    // and StockAlertPolicy::resolve(): gating on $order->isCancellable() here
    // would 403 before ProductionService's RuntimeException ("completed or
    // cancelled") ever gets a chance to produce a friendly redirect-with-errors
    // instead. The controller's update()/cancel() now catch that exception.
    public function update(User $user, ProductionOrder $order): bool
    {
        return $user->isProductionManager();
    }

    public function cancel(User $user, ProductionOrder $order): bool
    {
        return $user->isProductionManager();
    }
}
