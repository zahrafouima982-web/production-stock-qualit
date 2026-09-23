<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Component;
use App\Models\CorrectiveAction;
use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\QualityDefect;
use App\Models\QualityInspection;
use App\Models\StockAlert;
use App\Models\StockMovement;

/**
 * Read-only aggregation, one method per role dashboard — same "no
 * DB::transaction() needed" reasoning as TraceabilityService. Every number
 * here comes from existing tables; nothing new is stored. Kept to metrics
 * the architecture doc actually asked for (admin: global oversight;
 * production: orders + a quality signal; quality: inspection/defect
 * throughput; stock: levels + alerts) — no invented KPIs.
 */
class DashboardService
{
    public function adminStats(): array
    {
        return [
            'orders_by_status' => $this->ordersByStatus(),
            'inspection_totals' => $this->inspectionTotals(),
            'active_stock_alerts_count' => StockAlert::where('status', StockAlert::STATUS_ACTIVE)->count(),
            'pending_corrective_actions_count' => CorrectiveAction::whereIn('status', [
                CorrectiveAction::STATUS_OPEN,
                CorrectiveAction::STATUS_IN_PROGRESS,
                CorrectiveAction::STATUS_DONE,
            ])->count(),
            'recent_activity' => ActivityLog::with('user')->latest('created_at')->limit(10)->get(),
        ];
    }

    public function productionStats(): array
    {
        $orders = ProductionOrder::whereIn('status', [
            ProductionOrder::STATUS_PLANNED,
            ProductionOrder::STATUS_IN_PROGRESS,
        ])->get(['planned_quantity']);

        return [
            'orders_by_status' => $this->ordersByStatus(),
            'planned_quantity_total' => (int) $orders->sum('planned_quantity'),
            'produced_quantity_total' => (int) ProductionRecord::whereIn(
                'production_order_id',
                ProductionOrder::whereIn('status', [ProductionOrder::STATUS_PLANNED, ProductionOrder::STATUS_IN_PROGRESS])->pluck('id')
            )->sum('produced_quantity'),
            'recent_fail_inspections_count' => QualityInspection::where('result', QualityInspection::RESULT_FAIL)
                ->where('inspected_at', '>=', now()->subDays(30))
                ->count(),
        ];
    }

    public function qualityStats(): array
    {
        return [
            'inspection_totals' => $this->inspectionTotals(),
            'open_defects_count' => QualityDefect::whereIn('status', [
                QualityDefect::STATUS_OPEN,
                QualityDefect::STATUS_IN_PROGRESS,
            ])->count(),
            'pending_validation_count' => CorrectiveAction::where('status', CorrectiveAction::STATUS_DONE)->count(),
            'recent_inspections' => QualityInspection::with(['productionRecord.productionOrder.product', 'inspector'])
                ->latest('inspected_at')
                ->limit(10)
                ->get(),
        ];
    }

    public function stockStats(): array
    {
        $components = Component::where('is_active', true)->get(['current_quantity', 'safety_stock_threshold']);

        return [
            'total_components' => $components->count(),
            'critical_count' => $components->filter(
                fn ($c) => $c->current_quantity > 0 && $c->current_quantity <= $c->safety_stock_threshold
            )->count(),
            'out_of_stock_count' => $components->filter(fn ($c) => $c->current_quantity <= 0)->count(),
            'active_alerts_count' => StockAlert::where('status', StockAlert::STATUS_ACTIVE)->count(),
            'recent_movements' => StockMovement::with(['component', 'performedBy'])
                ->latest('performed_at')
                ->limit(10)
                ->get(),
        ];
    }

    private function ordersByStatus(): array
    {
        return ProductionOrder::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    private function inspectionTotals(): array
    {
        $pass = QualityInspection::where('result', QualityInspection::RESULT_PASS)->count();
        $fail = QualityInspection::where('result', QualityInspection::RESULT_FAIL)->count();
        $total = $pass + $fail;

        return [
            'pass' => $pass,
            'fail' => $fail,
            'pass_rate' => $total > 0 ? round(($pass / $total) * 100, 1) : null,
        ];
    }
}
