<?php

namespace App\Policies;

use App\Models\CorrectiveAction;
use App\Models\Role;
use App\Models\User;

class CorrectiveActionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, [Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER], true);
    }

    public function view(User $user, CorrectiveAction $action): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isQualityController();
    }

    // Once VALIDATED, a corrective action is closed history — no further edits.
    public function update(User $user, CorrectiveAction $action): bool
    {
        return $user->isQualityController() && $action->status !== CorrectiveAction::STATUS_VALIDATED;
    }

    // Only *who* is checked here (role). Whether the action is actually
    // eligible (DONE status, passing reinspection) is a workflow rule, not an
    // authorization rule — it's enforced in QualityService::validateCorrectiveAction(),
    // which throws a RuntimeException the controller turns into a friendly
    // validation error instead of a hard 403. Keeping both checks here would
    // silently mask that message behind a 403 (see Phase 4 bugfix notes).
    public function validate(User $user, CorrectiveAction $action): bool
    {
        return $user->isQualityController();
    }
}