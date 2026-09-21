<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\StockMovement;
use App\Models\User;

/**
 * ADMIN reads/supervises only, never records movements themselves — same
 * least-privilege rule as Production Orders. No update()/delete(): the
 * ledger is append-only, corrections happen via a new compensating
 * movement, never by editing history.
 */
class StockMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::STOCK_MANAGER], true);
    }

    public function view(User $user, StockMovement $movement): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isStockManager();
    }
}
