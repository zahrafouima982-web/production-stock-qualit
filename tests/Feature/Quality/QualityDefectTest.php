<?php

namespace Tests\Feature\Quality;

use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\QualityDefect;
use App\Models\QualityInspection;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityDefectTest extends TestCase
{
    use RefreshDatabase;

    protected QualityInspection $failedInspection;
    protected QualityInspection $passedInspection;
    protected User $qc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->qc = User::factory()->create(['role_id' => Role::where('name', Role::QUALITY_CONTROLLER)->value('id')]);
        $pm = User::factory()->create(['role_id' => Role::where('name', Role::PRODUCTION_MANAGER)->value('id')]);

        $product = Product::create(['code' => 'PRD-Q2', 'name' => 'Cable Set']);
        $line = ProductionLine::create(['code' => 'LINE-Q2', 'name' => 'Line Q2']);
        $order = ProductionOrder::create([
            'order_number' => 'PO-TEST-0002',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 100,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $pm->id,
        ]);
        $record = ProductionRecord::create([
            'production_order_id' => $order->id,
            'produced_quantity' => 50,
            'recorded_by' => $pm->id,
            'recorded_at' => now(),
        ]);

        $this->failedInspection = QualityInspection::create([
            'production_record_id' => $record->id,
            'inspector_id' => $this->qc->id,
            'result' => QualityInspection::RESULT_FAIL,
            'inspected_at' => now(),
        ]);

        $this->passedInspection = QualityInspection::create([
            'production_record_id' => $record->id,
            'inspector_id' => $this->qc->id,
            'result' => QualityInspection::RESULT_PASS,
            'inspected_at' => now(),
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    public function test_quality_controller_can_declare_a_defect_on_a_failed_inspection(): void
    {
        $response = $this->actingAs($this->qc)->post(
            route('quality.inspections.defects.store', $this->failedInspection),
            [
                'defect_type' => 'ELECTRICAL',
                'severity' => 'MAJOR',
                'description' => 'Open circuit on 3 sampled units.',
            ]
        );

        $defect = QualityDefect::first();

        $this->assertNotNull($defect);
        $this->assertSame('ELECTRICAL', $defect->defect_type);
        $this->assertSame('MAJOR', $defect->severity);
        $this->assertSame(QualityDefect::STATUS_OPEN, $defect->status);
        $this->assertSame($this->qc->id, $defect->detected_by);
        $response->assertRedirect(route('quality.defects.show', $defect));
    }

    public function test_defect_cannot_be_declared_on_a_passing_inspection(): void
    {
        $this->actingAs($this->qc)->post(
            route('quality.inspections.defects.store', $this->passedInspection),
            [
                'defect_type' => 'COSMETIC',
                'severity' => 'MINOR',
                'description' => 'Should be blocked.',
            ]
        )->assertSessionHasErrors('inspection');

        $this->assertSame(0, QualityDefect::count());
    }

    public function test_severity_must_be_a_valid_value(): void
    {
        $this->actingAs($this->qc)->post(
            route('quality.inspections.defects.store', $this->failedInspection),
            [
                'defect_type' => 'ELECTRICAL',
                'severity' => 'CATASTROPHIC', // not a valid enum value
                'description' => 'Bad severity.',
            ]
        )->assertSessionHasErrors('severity');
    }

    public function test_defect_status_moves_through_its_lifecycle_via_corrective_action(): void
    {
        $defect = QualityDefect::create([
            'quality_inspection_id' => $this->failedInspection->id,
            'defect_type' => 'ELECTRICAL',
            'description' => 'Test defect',
            'severity' => 'MAJOR',
            'detected_by' => $this->qc->id,
            'status' => QualityDefect::STATUS_OPEN,
        ]);

        // Creating a corrective action moves the defect to IN_PROGRESS.
        $this->actingAs($this->qc)->post(route('quality.defects.corrective-action.store', $defect), [
            'action_description' => 'Recalibrated crimp station.',
            'requires_reinspection' => true,
        ]);

        $defect->refresh();
        $this->assertSame(QualityDefect::STATUS_IN_PROGRESS, $defect->status);

        $action = $defect->correctiveActions()->first();

        // Marking the action DONE moves the defect to RESOLVED.
        $this->actingAs($this->qc)->put(route('quality.corrective-actions.update', $action), [
            'status' => 'DONE',
        ]);

        $defect->refresh();
        $this->assertSame(QualityDefect::STATUS_RESOLVED, $defect->status);
    }

    public function test_production_manager_can_view_but_not_create_defects(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $defect = QualityDefect::create([
            'quality_inspection_id' => $this->failedInspection->id,
            'defect_type' => 'ELECTRICAL',
            'description' => 'Visible to PM',
            'severity' => 'MINOR',
            'detected_by' => $this->qc->id,
        ]);

        $this->actingAs($pm)->get(route('quality.defects.show', $defect))->assertStatus(200);

        $this->actingAs($pm)->post(route('quality.inspections.defects.store', $this->failedInspection), [
            'defect_type' => 'OTHER',
            'severity' => 'MINOR',
            'description' => 'Blocked',
        ])->assertStatus(403);
    }

    public function test_stock_manager_has_no_access_to_defects(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $defect = QualityDefect::create([
            'quality_inspection_id' => $this->failedInspection->id,
            'defect_type' => 'ELECTRICAL',
            'description' => 'Hidden from stock',
            'severity' => 'MINOR',
            'detected_by' => $this->qc->id,
        ]);

        $this->actingAs($sm)->get(route('quality.defects.show', $defect))->assertStatus(403);
    }
}
