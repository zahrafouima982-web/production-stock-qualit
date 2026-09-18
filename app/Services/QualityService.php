<?php

namespace App\Services;

use App\Models\CorrectiveAction;
use App\Models\QualityDefect;
use App\Models\QualityInspection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * All Quality Inspection / Defect / Corrective Action business logic lives
 * here — Controllers only validate (via Form Requests) and delegate, same
 * convention as ProductionService.
 */
class QualityService
{
    public function __construct(private ActivityLogService $activityLog)
    {
    }

    /**
     * Creates an inspection together with its items in one transaction.
     * The overall result is derived from the items (any FAIL -> FAIL,
     * otherwise PASS) rather than set directly, per the architecture rule
     * that inspection results should be computed for consistency.
     *
     * Also serves as the reinspection path: passing an existing FAILED
     * inspection's production_record_id here creates a brand new row —
     * the old inspection is never touched, so full history is preserved.
     */
    public function createInspection(array $data, User $user): QualityInspection
    {
        return DB::transaction(function () use ($data, $user) {
            $result = $this->computeResult($data['items']);

            $inspection = QualityInspection::create([
                'production_record_id' => $data['production_record_id'],
                'inspector_id' => $user->id,
                'result' => $result,
                'inspected_at' => $data['inspected_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $inspection->inspectionItems()->create([
                    'criterion_name' => $item['criterion_name'],
                    'result' => $item['result'],
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            $this->activityLog->log(
                $user,
                'recorded quality inspection',
                $inspection,
                "Result: {$result} on production record #{$inspection->production_record_id}."
            );

            return $inspection->fresh('inspectionItems');
        });
    }

    /**
     * Any FAIL among the items fails the whole inspection. N_A items never
     * fail it. An all-PASS/N_A set of items passes.
     */
    private function computeResult(array $items): string
    {
        foreach ($items as $item) {
            if ($item['result'] === 'FAIL') {
                return QualityInspection::RESULT_FAIL;
            }
        }

        return QualityInspection::RESULT_PASS;
    }

    public function createDefect(array $data, QualityInspection $inspection, User $user): QualityDefect
    {
        if ($inspection->result !== QualityInspection::RESULT_FAIL) {
            throw new RuntimeException('Defects can only be declared against a FAILED inspection.');
        }

        return DB::transaction(function () use ($data, $inspection, $user) {
            $defect = $inspection->qualityDefects()->create([
                'defect_type' => $data['defect_type'],
                'description' => $data['description'],
                'severity' => $data['severity'],
                'detected_by' => $user->id,
                'status' => QualityDefect::STATUS_OPEN,
            ]);

            $this->activityLog->log(
                $user,
                'declared quality defect',
                $defect,
                "{$data['severity']} {$data['defect_type']} defect on inspection #{$inspection->id}."
            );

            return $defect;
        });
    }

    public function createCorrectiveAction(array $data, QualityDefect $defect, User $user): CorrectiveAction
    {
        return DB::transaction(function () use ($data, $defect, $user) {
            $action = $defect->correctiveActions()->create([
                'root_cause' => $data['root_cause'] ?? null,
                'action_description' => $data['action_description'],
                'responsible_user_id' => $data['responsible_user_id'] ?? $user->id,
                'status' => CorrectiveAction::STATUS_OPEN,
                'requires_reinspection' => $data['requires_reinspection'] ?? false,
            ]);

            $defect->update(['status' => QualityDefect::STATUS_IN_PROGRESS]);

            $this->activityLog->log($user, 'created corrective action', $action);

            return $action;
        });
    }

    /**
     * Keeps the parent defect's status roughly in sync with corrective action
     * progress. DONE == "Resolution" applied (per the workflow diagram) —
     * the defect moves to RESOLVED, but only validateCorrectiveAction() below
     * can move it all the way to CLOSED, since that step requires proof
     * (a passing reinspection).
     */
    public function updateCorrectiveAction(CorrectiveAction $action, array $data, User $user): CorrectiveAction
    {
        if ($action->status === CorrectiveAction::STATUS_VALIDATED) {
            throw new RuntimeException('This corrective action has already been validated and is closed history.');
        }

        $action->update([
            'root_cause' => $data['root_cause'] ?? $action->root_cause,
            'action_description' => $data['action_description'] ?? $action->action_description,
            'status' => $data['status'] ?? $action->status,
            'requires_reinspection' => $data['requires_reinspection'] ?? $action->requires_reinspection,
        ]);

        if ($action->status === CorrectiveAction::STATUS_DONE) {
            $action->qualityDefect->update(['status' => QualityDefect::STATUS_RESOLVED]);
        } elseif (in_array($action->status, [CorrectiveAction::STATUS_OPEN, CorrectiveAction::STATUS_IN_PROGRESS], true)) {
            $action->qualityDefect->update(['status' => QualityDefect::STATUS_IN_PROGRESS]);
        }

        $this->activityLog->log($user, 'updated corrective action', $action);

        return $action->fresh();
    }

    /**
     * Enforces the exact order given in the Phase 4 workflow diagram:
     * Corrective Action -> Resolution (DONE) -> Reinspection -> Validation.
     * A corrective action can only be validated once the SAME production
     * record has a later inspection whose result is PASS — i.e. the fix was
     * actually confirmed by a real reinspection, not just marked done.
     */
    public function validateCorrectiveAction(CorrectiveAction $action, User $user): CorrectiveAction
    {
        if ($action->status !== CorrectiveAction::STATUS_DONE) {
            throw new RuntimeException('Only a corrective action marked DONE can be validated.');
        }

        $productionRecordId = $action->qualityDefect->qualityInspection->production_record_id;

        // Ordered by id, not inspected_at: two inspections can land on the same
        // second in storage (timestamp columns aren't microsecond-precise),
        // and inspected_at is a user-editable field a stricter check shouldn't
        // trust for "did a reinspection really happen after this." Insertion
        // order (id) is both tie-free and harder to fake.
        $latestInspection = QualityInspection::where('production_record_id', $productionRecordId)
            ->latest('id')
            ->first();

        if (! $latestInspection || $latestInspection->result !== QualityInspection::RESULT_PASS) {
            throw new RuntimeException('A passing reinspection is required before this corrective action can be validated.');
        }

        return DB::transaction(function () use ($action, $user, $productionRecordId) {
            $action->update([
                'status' => CorrectiveAction::STATUS_VALIDATED,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]);

            $action->qualityDefect->update(['status' => QualityDefect::STATUS_CLOSED]);

            $this->activityLog->log(
                $user,
                'validated corrective action',
                $action,
                "Confirmed by a passing reinspection on production record #{$productionRecordId}."
            );

            return $action->fresh();
        });
    }
}