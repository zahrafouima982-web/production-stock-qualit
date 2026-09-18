<?php

namespace Tests\Feature\Quality;

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

class CorrectiveActionTest extends TestCase
{
    use RefreshDatabase;

    protected User $qc;
    protected ProductionRecord $record;
    protected QualityDefect $defect;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->qc = User::factory()->create(['role_id' => Role::where('name', Role::QUALITY_CONTROLLER)->value('id')]);
        $pm = User::factory()->create(['role_id' => Role::where('name', Role::PRODUCTION_MANAGER)->value('id')]);

        $product = Product::create(['code' => 'PRD-Q3', 'name' => 'Connector Assembly']);
        $line = ProductionLine::create(['code' => 'LINE-Q3', 'name' => 'Line Q3']);
        $order = ProductionOrder::create([
            'order_number' => 'PO-TEST-0003',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 100,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $pm->id,
        ]);
        $this->record = ProductionRecord::create([
            'production_order_id' => $order->id,
            'produced_quantity' => 50,
            'recorded_by' => $pm->id,
            'recorded_at' => now(),
        ]);

        $failedInspection = QualityInspection::create([
            'production_record_id' => $this->record->id,
            'inspector_id' => $this->qc->id,
            'result' => QualityInspection::RESULT_FAIL,
            'inspected_at' => now(),
        ]);

        $this->defect = QualityDefect::create([
            'quality_inspection_id' => $failedInspection->id,
            'defect_type' => 'ELECTRICAL',
            'description' => 'Open circuit',
            'severity' => 'MAJOR',
            'detected_by' => $this->qc->id,
            'status' => QualityDefect::STATUS_OPEN,
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    private function markActionDone(): CorrectiveAction
    {
        $this->actingAs($this->qc)->post(route('quality.defects.corrective-action.store', $this->defect), [
            'root_cause' => 'Crimp station out of tolerance',
            'action_description' => 'Recalibrated crimp station.',
            'requires_reinspection' => true,
        ]);

        $action = CorrectiveAction::first();

        $this->actingAs($this->qc)->put(route('quality.corrective-actions.update', $action), [
            'status' => 'DONE',
        ]);

        return $action->refresh();
    }

    public function test_quality_controller_can_create_a_corrective_action(): void
    {
        $response = $this->actingAs($this->qc)->post(route('quality.defects.corrective-action.store', $this->defect), [
            'root_cause' => 'Crimp pressure out of tolerance',
            'action_description' => 'Recalibrated and retrained operator.',
            'requires_reinspection' => true,
        ]);

        $action = CorrectiveAction::first();

        $this->assertNotNull($action);
        $this->assertSame(CorrectiveAction::STATUS_OPEN, $action->status);
        $this->assertTrue((bool) $action->requires_reinspection);
        $response->assertRedirect(route('quality.corrective-actions.show', $action));
    }

    public function test_validation_is_rejected_without_a_prior_done_status(): void
    {
        $this->actingAs($this->qc)->post(route('quality.defects.corrective-action.store', $this->defect), [
            'action_description' => 'Fix applied.',
        ]);
        $action = CorrectiveAction::first();

        // Still OPEN, never marked DONE.
        $this->actingAs($this->qc)->post(route('quality.corrective-actions.validate', $action))
            ->assertSessionHasErrors('validation');

        $this->assertSame(CorrectiveAction::STATUS_OPEN, $action->fresh()->status);
    }

    public function test_validation_is_rejected_without_a_passing_reinspection(): void
    {
        $action = $this->markActionDone();

        // DONE, but no reinspection has happened yet — still just the original FAIL.
        $this->actingAs($this->qc)->post(route('quality.corrective-actions.validate', $action))
            ->assertSessionHasErrors('validation');

        $this->assertSame(CorrectiveAction::STATUS_DONE, $action->fresh()->status);
    }

    public function test_validation_succeeds_after_a_passing_reinspection(): void
    {
        $action = $this->markActionDone();

        // Perform the reinspection required by the workflow diagram.
        $this->actingAs($this->qc)->post(route('quality.inspections.store'), [
            'production_record_id' => $this->record->id,
            'items' => [['criterion_name' => 'Continuity Test', 'result' => 'PASS']],
        ]);

        $response = $this->actingAs($this->qc)->post(route('quality.corrective-actions.validate', $action));

        $action->refresh();
        $this->assertSame(CorrectiveAction::STATUS_VALIDATED, $action->status);
        $this->assertSame($this->qc->id, $action->validated_by);
        $this->assertNotNull($action->validated_at);
        $this->assertSame(QualityDefect::STATUS_CLOSED, $action->qualityDefect->fresh()->status);
        $response->assertRedirect(route('quality.corrective-actions.show', $action));
    }

    public function test_a_validated_action_can_no_longer_be_updated(): void
    {
        $action = $this->markActionDone();

        $this->actingAs($this->qc)->post(route('quality.inspections.store'), [
            'production_record_id' => $this->record->id,
            'items' => [['criterion_name' => 'Continuity Test', 'result' => 'PASS']],
        ]);

        $this->actingAs($this->qc)->post(route('quality.corrective-actions.validate', $action));

        $this->actingAs($this->qc)
            ->put(route('quality.corrective-actions.update', $action->fresh()), ['status' => 'IN_PROGRESS'])
            ->assertStatus(403);
    }

    public function test_production_manager_cannot_create_or_validate_corrective_actions(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $this->actingAs($pm)->post(route('quality.defects.corrective-action.store', $this->defect), [
            'action_description' => 'Blocked',
        ])->assertStatus(403);

        $action = $this->markActionDone();

        $this->actingAs($pm)->post(route('quality.corrective-actions.validate', $action))->assertStatus(403);
    }

    public function test_stock_manager_has_no_access_to_corrective_actions(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $action = $this->markActionDone();

        $this->actingAs($sm)->get(route('quality.corrective-actions.show', $action))->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_corrective_actions(): void
    {
        // Built directly, not via markActionDone(), because that helper calls
        // $this->actingAs($this->qc) — which persists for the rest of the test
        // method. Using it here would leave the client authenticated as $qc,
        // so the request below wouldn't actually be a guest request at all.
        $action = CorrectiveAction::create([
            'quality_defect_id' => $this->defect->id,
            'root_cause' => 'N/A',
            'action_description' => 'N/A',
            'responsible_user_id' => $this->qc->id,
            'status' => CorrectiveAction::STATUS_DONE,
            'requires_reinspection' => false,
        ]);

        $this->get(route('quality.corrective-actions.show', $action))->assertRedirect(route('login'));
    }
}