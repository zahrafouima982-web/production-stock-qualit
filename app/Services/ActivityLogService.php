<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Thin wrapper around the activity_logs table (see Phase 1 architecture,
 * module 17). Called from other Services — never from Controllers directly —
 * so every module logs mutations the same way.
 */
class ActivityLogService
{
    public function log(User $user, string $action, Model $subject, ?string $description = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'description' => $description,
        ]);
    }
}
