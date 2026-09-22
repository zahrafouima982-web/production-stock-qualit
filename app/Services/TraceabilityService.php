<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ProductionOrder;
use Illuminate\Support\Collection;

/**
 * Read-only aggregation across Production, Quality, and the activity log —
 * no mutations, so no DB::transaction() needed here (unlike the other
 * Services). This exists because no single existing Policy/Controller owns
 * "the full history of one production order" — it's inherently cross-module.
 *
 * KNOWN GAP (by design, not an oversight): Components and Stock Movements
 * are NOT linked here. There is no foreign key from stock_movements to
 * production_orders — that was a deliberate Phase 1 decision to avoid
 * building a BOM/consumption-tracking module that wasn't asked for. The
 * traceability view says so explicitly rather than faking a match on the
 * free-text `reference` field, which would be unreliable.
 */
class TraceabilityService
{
    /**
     * Eager-loads the full chain for one order:
     * order -> records -> inspections -> items + defects -> corrective actions.
     */
    public function loadOrderChain(ProductionOrder $order): ProductionOrder
    {
        $order->load([
            'product',
            'productionLine',
            'createdBy',
            'productionRecords' => fn ($q) => $q->orderBy('recorded_at'),
            'productionRecords.recordedBy',
            'productionRecords.qualityInspections' => fn ($q) => $q->orderBy('inspected_at'),
            'productionRecords.qualityInspections.inspector',
            'productionRecords.qualityInspections.inspectionItems',
            'productionRecords.qualityInspections.qualityDefects.detectedBy',
            'productionRecords.qualityInspections.qualityDefects.correctiveActions.responsibleUser',
            'productionRecords.qualityInspections.qualityDefects.correctiveActions.validatedBy',
        ]);

        return $order;
    }

    /**
     * Every activity_logs entry whose subject is the order itself, or any
     * production record / quality inspection / quality defect / corrective
     * action that belongs to it — merged into one chronological "who did
     * what, when" timeline. Answers the architecture doc's traceability
     * question "which user performed each important operation?" in one place
     * rather than scattering it across per-relation actor columns.
     */
    public function buildActivityTimeline(ProductionOrder $order): Collection
    {
        $subjectPairs = [['App\\Models\\ProductionOrder', $order->id]];

        foreach ($order->productionRecords as $record) {
            $subjectPairs[] = ['App\\Models\\ProductionRecord', $record->id];

            foreach ($record->qualityInspections as $inspection) {
                $subjectPairs[] = ['App\\Models\\QualityInspection', $inspection->id];

                foreach ($inspection->qualityDefects as $defect) {
                    $subjectPairs[] = ['App\\Models\\QualityDefect', $defect->id];

                    foreach ($defect->correctiveActions as $action) {
                        $subjectPairs[] = ['App\\Models\\CorrectiveAction', $action->id];
                    }
                }
            }
        }

        return ActivityLog::where(function ($query) use ($subjectPairs) {
            foreach ($subjectPairs as [$type, $id]) {
                $query->orWhere(fn ($q) => $q->where('subject_type', $type)->where('subject_id', $id));
            }
        })
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Search used by the traceability landing page — by order number
     * (partial match) or product name/code.
     */
    public function searchOrders(?string $term, int $limit = 20): Collection
    {
        return ProductionOrder::query()
            ->with(['product', 'productionLine'])
            ->when($term, function ($query, $term) {
                $query->where('order_number', 'like', "%{$term}%")
                    ->orWhereHas('product', function ($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")->orWhere('code', 'like', "%{$term}%");
                    });
            })
            ->latest()
            ->limit($limit)
            ->get();
    }
}
