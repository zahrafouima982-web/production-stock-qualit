<?php

namespace Tests\Feature\Traceability;

use App\Models\CorrectiveAction;
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

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    protected User $pm;
    protected User $qc;
    protected ProductionOrder $order;
    protected ProductionRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->pm = User::factory()->create(['role_id' => Role::where('name', Role::PRODUCTION_MANAGER)->value('id')]);
        $this->qc = User::factory()->create(['role_id' => Role::where('name', Role::QUALITY_CONTROLLER)->value('id')]);

        $product = Product::create(['code' => 'PRD-TR-1', 'name' => 'Traced Harness']);
        $line = ProductionLine::create(['code' => 'LINE-TR-1', 'name' => 'Trace Line']);

        $this->order = ProductionOrder::create([
            'order_number' => 'PO-TRACE-0001',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 100,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $this->pm->id,
        ]);

        $this->record = ProductionRecord::create([
            'production_order_id' => $this->order->id,
            'produced_quantity' => 50,
            'recorded_by' => $this->pm->id,
            'recorded_at' => now(),
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    public function test_all_four_roles_can_access_the_traceability_search_page(): void
    {
        foreach ([Role::ADMIN, Role::PRODUCTION_MANAGER, Role::QUALITY_CONTROLLER, Role::STOCK_MANAGER] as $roleName) {
            $user = $this->userWithRole($roleName);
            $this->actingAs($user)->get(route('traceability.index'))->assertStatus(200);
        }
    }

    public function test_unauthenticated_user_cannot_access_traceability(): void
    {
        $this->get(route('traceability.index'))->assertRedirect(route('login'));
        $this->get(route('traceability.show', $this->order))->assertRedirect(route('login'));
    }

    public function test_search_finds_order_by_order_number(): void
    {
        $response = $this->actingAs($this->pm)->get(route('traceability.index', ['q' => 'PO-TRACE-0001']));

        $response->assertStatus(200);
        $response->assertSee('PO-TRACE-0001');
    }

    public function test_search_finds_order_by_product_name(): void
    {
        $response = $this->actingAs($this->pm)->get(route('traceability.index', ['q' => 'Traced Harness']));

        $response->assertSee('PO-TRACE-0001');
    }

    public function test_trace_page_shows_the_full_chain_down_to_corrective_actions(): void
    {
        $inspection = QualityInspection::create([
            'production_record_id' => $this->record->id,
            'inspector_id' => $this->qc->id,
            'result' => QualityInspection::RESULT_FAIL,
            'inspected_at' => now(),
        ]);

        $defect = QualityDefect::create([
            'quality_inspection_id' => $inspection->id,
            'defect_type' => 'ELECTRICAL',
            'description' => 'Open circuit',
            'severity' => 'MAJOR',
            'detected_by' => $this->qc->id,
            'status' => QualityDefect::STATUS_OPEN,
        ]);

        $action = CorrectiveAction::create([
            'quality_defect_id' => $defect->id,
            'action_description' => 'Recalibrated crimp station',
            'responsible_user_id' => $this->pm->id,
            'status' => CorrectiveAction::STATUS_OPEN,
        ]);

        $response = $this->actingAs($this->qc)->get(route('traceability.show', $this->order));

        $response->assertStatus(200);
        $response->assertSee($this->order->order_number);
        $response->assertSee('Inspection #' . $inspection->id);
        $response->assertSee('Defect #' . $defect->id);
        $response->assertSee('Corrective Action #' . $action->id);
    }

    public function test_trace_page_does_not_leak_data_from_another_order(): void
    {
        $otherProduct = Product::create(['code' => 'PRD-TR-2', 'name' => 'Other Product']);
        $otherLine = ProductionLine::create(['code' => 'LINE-TR-2', 'name' => 'Other Line']);
        $otherOrder = ProductionOrder::create([
            'order_number' => 'PO-TRACE-9999',
            'product_id' => $otherProduct->id,
            'production_line_id' => $otherLine->id,
            'planned_quantity' => 20,
            'status' => ProductionOrder::STATUS_PLANNED,
            'created_by' => $this->pm->id,
        ]);
        $otherRecord = ProductionRecord::create([
            'production_order_id' => $otherOrder->id,
            'produced_quantity' => 5,
            'recorded_by' => $this->pm->id,
            'recorded_at' => now(),
        ]);
        $otherInspection = QualityInspection::create([
            'production_record_id' => $otherRecord->id,
            'inspector_id' => $this->qc->id,
            'result' => QualityInspection::RESULT_PASS,
            'inspected_at' => now(),
        ]);

        $response = $this->actingAs($this->pm)->get(route('traceability.show', $this->order));

        $response->assertDontSee('Inspection #' . $otherInspection->id);
        $response->assertDontSee('PO-TRACE-9999');
    }

    public function test_activity_timeline_includes_actions_taken_on_this_orders_chain(): void
    {
        // Creating an inspection through the real endpoint logs an activity entry.
        $this->actingAs($this->qc)->post(route('quality.inspections.store'), [
            'production_record_id' => $this->record->id,
            'items' => [['criterion_name' => 'Continuity Test', 'result' => 'PASS']],
        ]);

        $response = $this->actingAs($this->pm)->get(route('traceability.show', $this->order));

        $response->assertSee('recorded quality inspection');
    }
}
