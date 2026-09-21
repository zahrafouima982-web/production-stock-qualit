<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\StockAlert;
use App\Models\User;

class StockAlertPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::STOCK_MANAGER], true);
    }

    public function view(User $user, StockAlert $alert): bool
    {
        return $this->viewAny($user);
    }

    // Only role is checked here — same lesson as CorrectiveActionPolicy::validate()
    // in Phase 4: gating on $alert->status here would 403 before the Service's
    // RuntimeException ("already resolved") ever gets a chance to produce a
    // friendly redirect-with-errors instead.
    public function resolve(User $user, StockAlert $alert): bool
    {
        return $user->isStockManager();
    }
}
