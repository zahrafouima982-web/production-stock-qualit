<?php

namespace Database\Seeders;

use App\Models\Component;
use App\Models\CorrectiveAction;
use App\Models\InspectionItem;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\QualityDefect;
use App\Models\QualityInspection;
use App\Models\Role;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $productionManager = User::where('email', 'production@pqtms.test')->first();
            $qualityController = User::where('email', 'quality@pqtms.test')->first();
            $stockManager = User::where('email', 'stock@pqtms.test')->first();

            // --- Master data -------------------------------------------------

            $lineA = ProductionLine::updateOrCreate(
                ['code' => 'LINE-A'],
                ['name' => 'Wiring Line A', 'location' => 'Building 1 - Zone 1', 'is_active' => true]
            );

            $lineB = ProductionLine::updateOrCreate(
                ['code' => 'LINE-B'],
                ['name' => 'Wiring Line B', 'location' => 'Building 1 - Zone 2', 'is_active' => true]
            );

            $harness = Product::updateOrCreate(
                ['code' => 'PRD-HARN-001'],
                ['name' => 'Engine Wiring Harness', 'description' => 'Main engine bay wiring harness assembly.', 'is_active' => true]
            );

            $connector = Component::updateOrCreate(
                ['code' => 'CMP-CONN-100'],
                [
                    'name' => 'Sealed Connector 4-Pin',
                    'unit_of_measure' => 'pcs',
                    'current_quantity' => 500,
                    'safety_stock_threshold' => 200,
                    'is_active' => true,
                ]
            );

            $terminal = Component::updateOrCreate(
                ['code' => 'CMP-TERM-050'],
                [
                    'name' => 'Crimp Terminal Type B',
                    'unit_of_measure' => 'pcs',
                    'current_quantity' => 150,
                    'safety_stock_threshold' => 300, // intentionally already below threshold
                    'is_active' => true,
                ]
            );

            $cableReel = Component::updateOrCreate(
                ['code' => 'CMP-CABLE-020'],
                [
                    'name' => 'Automotive Cable 2.5mm - Reel',
                    'unit_of_measure' => 'meters',
                    'current_quantity' => 1000,
                    'safety_stock_threshold' => 250,
                    'is_active' => true,
                ]
            );

            // --- Production workflow -----------------------------------------

            $order = ProductionOrder::updateOrCreate(
                ['order_number' => 'PO-2026-0001'],
                [
                    'product_id' => $harness->id,
                    'production_line_id' => $lineA->id,
                    'planned_quantity' => 200,
                    'status' => ProductionOrder::STATUS_IN_PROGRESS,
                    'created_by' => $productionManager->id,
                    'start_date' => now()->subDays(2),
                ]
            );

            $record = ProductionRecord::firstOrCreate(
                ['production_order_id' => $order->id, 'shift' => 'Morning'],
                [
                    'produced_quantity' => 180,
                    'recorded_by' => $productionManager->id,
                    'recorded_at' => now()->subDay(),
                ]
            );

            // --- Quality workflow (FAIL -> defect -> corrective action -> reinspection PASS) -

            $failedInspection = QualityInspection::firstOrCreate(
                ['production_record_id' => $record->id, 'result' => QualityInspection::RESULT_FAIL],
                [
                    'inspector_id' => $qualityController->id,
                    'inspected_at' => now()->subHours(20),
                    'notes' => 'Initial inspection - continuity failure on batch sample.',
                ]
            );

            InspectionItem::firstOrCreate(
                ['quality_inspection_id' => $failedInspection->id, 'criterion_name' => 'Continuity Test'],
                ['result' => 'FAIL', 'remarks' => 'Open circuit detected on 3 of 20 sampled units.']
            );

            InspectionItem::firstOrCreate(
                ['quality_inspection_id' => $failedInspection->id, 'criterion_name' => 'Dimensional Check'],
                ['result' => 'PASS']
            );

            $defect = QualityDefect::firstOrCreate(
                ['quality_inspection_id' => $failedInspection->id, 'defect_type' => 'ELECTRICAL'],
                [
                    'description' => 'Open circuit on continuity test for sampled units.',
                    'severity' => 'MAJOR',
                    'detected_by' => $qualityController->id,
                    'status' => QualityDefect::STATUS_RESOLVED,
                ]
            );

            $correctiveAction = CorrectiveAction::firstOrCreate(
                ['quality_defect_id' => $defect->id],
                [
                    'root_cause' => 'Crimp terminal seating pressure out of tolerance on Line A crimp station.',
                    'action_description' => 'Recalibrated crimp station and retrained operator on seating procedure.',
                    'responsible_user_id' => $productionManager->id,
                    'status' => CorrectiveAction::STATUS_VALIDATED,
                    'validated_by' => $qualityController->id,
                    'validated_at' => now()->subHours(10),
                    'requires_reinspection' => true,
                ]
            );

            // Reinspection = a new row on the same production_record_id.
            $reinspection = QualityInspection::firstOrCreate(
                ['production_record_id' => $record->id, 'result' => QualityInspection::RESULT_PASS],
                [
                    'inspector_id' => $qualityController->id,
                    'inspected_at' => now()->subHours(8),
                    'notes' => 'Reinspection after crimp station recalibration - all samples pass.',
                ]
            );

            InspectionItem::firstOrCreate(
                ['quality_inspection_id' => $reinspection->id, 'criterion_name' => 'Continuity Test'],
                ['result' => 'PASS']
            );

            // --- Stock workflow (movements + one triggered alert) ------------

            StockMovement::firstOrCreate(
                [
                    'component_id' => $terminal->id,
                    'type' => StockMovement::TYPE_OUT,
                    'reference' => 'PO-2026-0001',
                ],
                [
                    'quantity' => 50,
                    'performed_by' => $stockManager->id,
                    'performed_at' => now()->subDay(),
                ]
            );

            StockMovement::firstOrCreate(
                [
                    'component_id' => $cableReel->id,
                    'type' => StockMovement::TYPE_IN,
                    'reference' => 'SUPPLIER-INV-4521',
                ],
                [
                    'quantity' => 200,
                    'performed_by' => $stockManager->id,
                    'performed_at' => now()->subDays(3),
                ]
            );

            // Terminal component was seeded already below its safety_stock_threshold
            // (150 <= 300) to demonstrate an active alert without needing the
            // Stock service logic, which will be implemented in feature/stock.
            StockAlert::firstOrCreate(
                ['component_id' => $terminal->id, 'status' => StockAlert::STATUS_ACTIVE],
                ['triggered_at' => now()->subHours(12)]
            );
        });
    }
}
