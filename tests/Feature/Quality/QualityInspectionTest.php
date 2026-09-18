<?php

namespace Tests\Feature\Quality;

use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\QualityInspection;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QualityInspectionTest extends TestCase
{
    use RefreshDatabase;

    protected ProductionRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $product = Product::create(['code' => 'PRD-Q1', 'name' => 'Wiring Harness']);
        $line = ProductionLine::create(['code' => 'LINE-Q1', 'name' => 'Line Q1']);
        $order = ProductionOrder::create([
            'order_number' => 'PO-TEST-0001',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 100,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $this->userWithRole(Role::PRODUCTION_MANAGER)->id,
        ]);

        $this->record = ProductionRecord::create([
            'production_order_id' => $order->id,
            'produced_quantity' => 50,
            'recorded_by' => User::first()->id,
            'recorded_at' => now(),
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    private function validInspectionPayload(array $overrides = []): array
    {
        return array_merge([
            'production_record_id' => $this->record->id,
            'inspected_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Routine check',
            'items' => [
                ['criterion_name' => 'Continuity Test', 'result' => 'PASS'],
                ['criterion_name' => 'Dimensional Check', 'result' => 'PASS'],
            ],
        ], $overrides);
    }

    public function test_quality_controller_can_create_a_passing_inspection(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $response = $this->actingAs($qc)->post(route('quality.inspections.store'), $this->validInspectionPayload());

        $inspection = QualityInspection::first();

        $this->assertNotNull($inspection);
        $this->assertSame($this->record->id, $inspection->production_record_id);
        $this->assertSame($qc->id, $inspection->inspector_id);
        $this->assertSame(QualityInspection::RESULT_PASS, $inspection->result);
        $this->assertCount(2, $inspection->inspectionItems);
        $response->assertRedirect(route('quality.inspections.show', $inspection));
    }

    public function test_any_failed_item_fails_the_whole_inspection(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->post(route('quality.inspections.store'), $this->validInspectionPayload([
            'items' => [
                ['criterion_name' => 'Continuity Test', 'result' => 'FAIL', 'remarks' => 'Open circuit'],
                ['criterion_name' => 'Dimensional Check', 'result' => 'PASS'],
            ],
        ]));

        $inspection = QualityInspection::first();

        $this->assertSame(QualityInspection::RESULT_FAIL, $inspection->result);
    }

    public function test_inspection_requires_at_least_one_item(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)
            ->post(route('quality.inspections.store'), $this->validInspectionPayload(['items' => []]))
            ->assertSessionHasErrors('items');

        $this->assertSame(0, QualityInspection::count());
    }

    public function test_production_manager_can_view_but_not_create_inspections(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $this->actingAs($pm)->get(route('quality.inspections.index'))->assertStatus(200);

        $this->actingAs($pm)
            ->post(route('quality.inspections.store'), $this->validInspectionPayload())
            ->assertStatus(403);
    }

    public function test_admin_can_view_but_not_create_inspections(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)->get(route('quality.inspections.index'))->assertStatus(200);

        $this->actingAs($admin)
            ->post(route('quality.inspections.store'), $this->validInspectionPayload())
            ->assertStatus(403);
    }

    public function test_stock_manager_has_no_access_to_quality_inspections(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->get(route('quality.inspections.index'))->assertStatus(403);
        $this->actingAs($sm)->get(route('quality.inspections.create'))->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_quality_inspections(): void
    {
        $this->get(route('quality.inspections.index'))->assertRedirect(route('login'));
    }

    public function test_reinspection_creates_a_new_row_and_preserves_the_original(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->post(route('quality.inspections.store'), $this->validInspectionPayload([
            'items' => [['criterion_name' => 'Continuity Test', 'result' => 'FAIL']],
        ]));

        $failedInspection = QualityInspection::first();
        $this->assertSame(QualityInspection::RESULT_FAIL, $failedInspection->result);

        // Reinspect: same production_record_id, brand new row.
        $this->actingAs($qc)->post(route('quality.inspections.store'), $this->validInspectionPayload([
            'items' => [['criterion_name' => 'Continuity Test', 'result' => 'PASS']],
        ]));

        $this->assertSame(2, QualityInspection::count());

        $failedInspection->refresh();
        $this->assertSame(QualityInspection::RESULT_FAIL, $failedInspection->result, 'Original inspection must never be overwritten.');

        $latest = QualityInspection::latest('id')->first();
        $this->assertSame(QualityInspection::RESULT_PASS, $latest->result);
        $this->assertSame($this->record->id, $latest->production_record_id);
    }

    public function test_reinspect_action_requires_quality_controller_role(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);
        $this->actingAs($qc)->post(route('quality.inspections.store'), $this->validInspectionPayload([
            'items' => [['criterion_name' => 'Continuity Test', 'result' => 'FAIL']],
        ]));
        $inspection = QualityInspection::first();

        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $this->actingAs($pm)->post(route('quality.inspections.reinspect', $inspection))->assertStatus(403);

        $this->actingAs($qc)->post(route('quality.inspections.reinspect', $inspection))
            ->assertRedirect(route('quality.inspections.create', ['production_record_id' => $inspection->production_record_id]));
    }
}
