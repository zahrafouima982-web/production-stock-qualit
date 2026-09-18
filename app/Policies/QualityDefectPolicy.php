<?php

namespace App\Policies;

use App\Models\QualityDefect;
use App\Models\Role;
use App\Models\User;

class QualityDefectPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, QualityDefect $defect): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isQualityController();
    }

    public function update(User $user, QualityDefect $defect): bool
    {
        return $user->isQualityController();
    }
}
