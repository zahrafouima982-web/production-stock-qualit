<?php

namespace App\Services;

use App\Models\Component;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * All Component / Stock Movement / Stock Alert business logic lives here —
 * Controllers only validate (via Form Requests) and delegate, same
 * convention as ProductionService and QualityService.
 *
 * This is the one module where concurrency actually matters: two OUT
 * movements racing on the same component could both read a "sufficient"
 * quantity before either writes, over-drawing stock. Every mutation here
 * runs inside DB::transaction() with lockForUpdate() on the component row,
 * per the architecture's stock business logic rules.
 */
class StockService
{
    public function __construct(private ActivityLogService $activityLog)
    {
    }

    public function recordIn(array $data, User $user): StockMovement
    {
        return DB::transaction(function () use ($data, $user) {
            $component = Component::whereKey($data['component_id'])->lockForUpdate()->firstOrFail();

            $movement = $component->stockMovements()->create([
                'type' => StockMovement::TYPE_IN,
                'quantity' => $data['quantity'],
                'reference' => $data['reference'] ?? null,
                'performed_by' => $user->id,
                'performed_at' => $data['performed_at'] ?? now(),
            ]);

            $component->increment('current_quantity', $data['quantity']);
            $component->refresh();

            $this->evaluateAlertStatus($component, $user);

            $this->activityLog->log(
                $user,
                'recorded stock IN',
                $movement,
                "+{$data['quantity']} {$component->unit_of_measure} on {$component->code} (new balance: {$component->current_quantity})."
            );

            return $movement;
        });
    }

    public function recordOut(array $data, User $user): StockMovement
    {
        return DB::transaction(function () use ($data, $user) {
            $component = Component::whereKey($data['component_id'])->lockForUpdate()->firstOrFail();

            if ($data['quantity'] > $component->current_quantity) {
                throw new RuntimeException(
                    "Cannot remove {$data['quantity']} {$component->unit_of_measure}: only {$component->current_quantity} available for {$component->code}."
                );
            }

            $movement = $component->stockMovements()->create([
                'type' => StockMovement::TYPE_OUT,
                'quantity' => $data['quantity'],
                'reference' => $data['reference'] ?? null,
                'performed_by' => $user->id,
                'performed_at' => $data['performed_at'] ?? now(),
            ]);

            $component->decrement('current_quantity', $data['quantity']);
            $component->refresh();

            $this->evaluateAlertStatus($component, $user);

            $this->activityLog->log(
                $user,
                'recorded stock OUT',
                $movement,
                "-{$data['quantity']} {$component->unit_of_measure} on {$component->code} (new balance: {$component->current_quantity})."
            );

            return $movement;
        });
    }

    /**
     * Manual override on top of the automatic resolution in
     * evaluateAlertStatus(). Distinguished from an auto-resolve by
     * resolved_by being set to the acting user rather than left null.
     */
    public function resolveAlert(StockAlert $alert, User $user): StockAlert
    {
        if ($alert->status !== StockAlert::STATUS_ACTIVE) {
            throw new RuntimeException('This alert is already resolved.');
        }

        $alert->update([
            'status' => StockAlert::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => $user->id,
        ]);

        $this->activityLog->log($user, 'manually resolved stock alert', $alert);

        return $alert->fresh();
    }

    /**
     * Called from inside the same locked transaction as the triggering
     * movement — this is what prevents duplicate ACTIVE alerts under
     * concurrency (see components migration note on why this can't be a DB
     * constraint instead).
     */
    private function evaluateAlertStatus(Component $component, User $user): void
    {
        $isLowOrOut = $component->current_quantity <= $component->safety_stock_threshold;

        $activeAlert = $component->stockAlerts()->where('status', StockAlert::STATUS_ACTIVE)->first();

        if ($isLowOrOut && ! $activeAlert) {
            $alert = $component->stockAlerts()->create([
                'status' => StockAlert::STATUS_ACTIVE,
                'triggered_at' => now(),
            ]);

            $this->activityLog->log(
                $user,
                'triggered stock alert',
                $alert,
                "{$component->code} at {$component->current_quantity} {$component->unit_of_measure} (threshold: {$component->safety_stock_threshold})."
            );

            return;
        }

        if (! $isLowOrOut && $activeAlert) {
            // Auto-resolved by the movement itself — resolved_by stays null
            // to distinguish this from a manual resolveAlert() call.
            $activeAlert->update([
                'status' => StockAlert::STATUS_RESOLVED,
                'resolved_at' => now(),
            ]);

            $this->activityLog->log(
                $user,
                'auto-resolved stock alert',
                $activeAlert,
                "{$component->code} back to {$component->current_quantity} {$component->unit_of_measure}, above threshold."
            );
        }
    }
}
