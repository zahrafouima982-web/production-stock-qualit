<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * All Production Order / Production Record business logic lives here —
 * Controllers only validate (via Form Requests) and delegate.
 */
class ProductionService
{
    public function __construct(private ActivityLogService $activityLog)
    {
    }

    public function createOrder(array $data, User $user): ProductionOrder
    {
        return DB::transaction(function () use ($data, $user) {
            $order = ProductionOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'product_id' => $data['product_id'],
                'production_line_id' => $data['production_line_id'],
                'planned_quantity' => $data['planned_quantity'],
                'status' => ProductionOrder::STATUS_PLANNED,
                'created_by' => $user->id,
                'start_date' => $data['start_date'] ?? null,
            ]);

            $this->activityLog->log(
                $user,
                'created production order',
                $order,
                "Order {$order->order_number} planned for {$order->planned_quantity} units."
            );

            return $order;
        });
    }

    public function updateOrder(ProductionOrder $order, array $data, User $user): ProductionOrder
    {
        if (! $order->isCancellable()) {
            throw new RuntimeException('This order is completed or cancelled and can no longer be edited.');
        }

        $order->update([
            'product_id' => $data['product_id'],
            'production_line_id' => $data['production_line_id'],
            'planned_quantity' => $data['planned_quantity'],
            'start_date' => $data['start_date'] ?? null,
        ]);

        $this->activityLog->log($user, 'updated production order', $order);

        return $order->fresh();
    }

    public function cancelOrder(ProductionOrder $order, User $user): ProductionOrder
    {
        if (! $order->isCancellable()) {
            throw new RuntimeException('This order can no longer be cancelled.');
        }

        $order->update([
            'status' => ProductionOrder::STATUS_CANCELLED,
            'end_date' => now(),
        ]);

        $this->activityLog->log($user, 'cancelled production order', $order);

        return $order->fresh();
    }

    public function recordProduction(ProductionOrder $order, array $data, User $user): ProductionRecord
    {
        return DB::transaction(function () use ($order, $data, $user) {
            $record = $order->productionRecords()->create([
                'produced_quantity' => $data['produced_quantity'],
                'recorded_by' => $user->id,
                'recorded_at' => $data['recorded_at'] ?? now(),
                'shift' => $data['shift'] ?? null,
            ]);

            $this->syncOrderStatus($order);

            $this->activityLog->log(
                $user,
                'recorded production',
                $record,
                "{$record->produced_quantity} units recorded against order {$order->order_number}."
            );

            return $record;
        });
    }

    public function updateRecord(ProductionRecord $record, array $data, User $user): ProductionRecord
    {
        $record->update([
            'produced_quantity' => $data['produced_quantity'],
            'recorded_at' => $data['recorded_at'] ?? $record->recorded_at,
            'shift' => $data['shift'] ?? null,
        ]);

        $this->syncOrderStatus($record->productionOrder);

        $this->activityLog->log($user, 'updated production record', $record);

        return $record->fresh();
    }

    /**
     * PLANNED -> IN_PROGRESS on the first record; -> COMPLETED once the
     * cumulative produced quantity reaches the planned quantity. A cancelled
     * order is left alone — recording against a cancelled order is prevented
     * at the controller/policy level before this is ever reached.
     */
    private function syncOrderStatus(ProductionOrder $order): void
    {
        if ($order->status === ProductionOrder::STATUS_CANCELLED) {
            return;
        }

        $totalProduced = $order->productionRecords()->sum('produced_quantity');

        $order->status = $totalProduced >= $order->planned_quantity
            ? ProductionOrder::STATUS_COMPLETED
            : ProductionOrder::STATUS_IN_PROGRESS;

        if ($order->status === ProductionOrder::STATUS_COMPLETED) {
            $order->end_date = now();
        }

        $order->save();
    }

    /**
     * NOTE: count-then-format has a small race window under heavy concurrent
     * order creation (acceptable at this scope — order creation is a low-
     * frequency, single-department action, unlike stock movements). Revisit
     * with a dedicated sequence table if that ever changes.
     */
    private function generateOrderNumber(): string
    {
        $year = now()->format('Y');
        $sequence = ProductionOrder::whereYear('created_at', now()->year)->count() + 1;

        return sprintf('PO-%s-%04d', $year, $sequence);
    }
}
