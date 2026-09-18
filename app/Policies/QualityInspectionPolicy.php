<?php

namespace App\Policies;

use App\Models\QualityInspection;
use App\Models\Role;
use App\Models\User;

/**
 * Same read distribution as the Production policies: ADMIN and
 * PRODUCTION_MANAGER can read (supervise / consult), only QUALITY_CONTROLLER
 * writes. No update()/delete() — an inspection is an immutable record of what
 * was observed at a point in time; corrections happen via a new inspection
 * (reinspect()), never by editing history.
 */
class QualityInspectionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, QualityInspection $inspection): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isQualityController();
    }

    public function reinspect(User $user, QualityInspection $inspection): bool
    {
        return $user->isQualityController();
    }
}
