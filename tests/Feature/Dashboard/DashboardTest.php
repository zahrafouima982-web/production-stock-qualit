<?php

namespace Tests\Feature\Dashboard;

use App\Models\CorrectiveAction;
use App\Models\Component;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionRecord;
use App\Models\QualityDefect;
use App\Models\QualityInspection;
use App\Models\Role;
use App\Models\StockAlert;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    public function test_each_role_can_reach_its_own_dashboard(): void
    {
        $this->actingAs($this->userWithRole(Role::ADMIN))->get(route('dashboard'))->assertStatus(200);
        $this->actingAs($this->userWithRole(Role::PRODUCTION_MANAGER))->get(route('production.dashboard'))->assertStatus(200);
        $this->actingAs($this->userWithRole(Role::QUALITY_CONTROLLER))->get(route('quality.dashboard'))->assertStatus(200);
        $this->actingAs($this->userWithRole(Role::STOCK_MANAGER))->get(route('stock.dashboard'))->assertStatus(200);
    }

    public function test_admin_dashboard_reflects_real_data(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $product = Product::create(['code' => 'PRD-DASH-1', 'name' => 'Dashboard Product']);
        $line = ProductionLine::create(['code' => 'LINE-DASH-1', 'name' => 'Dashboard Line']);

        ProductionOrder::create([
            'order_number' => 'PO-DASH-0001',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 50,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $pm->id,
        ]);

        $component = Component::create([
            'code' => 'CMP-DASH-1',
            'name' => 'Dashboard Component',
            'unit_of_measure' => 'pcs',
            'current_quantity' => 5,
            'safety_stock_threshold' => 20,
        ]);
        StockAlert::create([
            'component_id' => $component->id,
            'status' => StockAlert::STATUS_ACTIVE,
            'triggered_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('active_stock_alerts_count', 1);
        $response->assertViewHas('orders_by_status', ['IN_PROGRESS' => 1]);
    }

    public function test_quality_dashboard_shows_pass_fail_totals_and_recent_inspections(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $product = Product::create(['code' => 'PRD-DASH-2', 'name' => 'Quality Dash Product']);
        $line = ProductionLine::create(['code' => 'LINE-DASH-2', 'name' => 'Quality Dash Line']);
        $order = ProductionOrder::create([
            'order_number' => 'PO-DASH-0002',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 50,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $pm->id,
        ]);
        $record = ProductionRecord::create([
            'production_order_id' => $order->id,
            'produced_quantity' => 10,
            'recorded_by' => $pm->id,
            'recorded_at' => now(),
        ]);

        $failedInspection = QualityInspection::create([
            'production_record_id' => $record->id,
            'inspector_id' => $qc->id,
            'result' => QualityInspection::RESULT_FAIL,
            'inspected_at' => now(),
        ]);

        QualityDefect::create([
            'quality_inspection_id' => $failedInspection->id,
            'defect_type' => 'ELECTRICAL',
            'description' => 'Test defect',
            'severity' => 'MAJOR',
            'detected_by' => $qc->id,
            'status' => QualityDefect::STATUS_OPEN,
        ]);

        $response = $this->actingAs($qc)->get(route('quality.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('open_defects_count', 1);
        $response->assertViewHas('inspection_totals', fn ($totals) => $totals['fail'] === 1 && $totals['pass'] === 0);
        $response->assertSee('PO-DASH-0002');
    }

    public function test_stock_dashboard_shows_critical_and_out_of_stock_counts(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        Component::create([
            'code' => 'CMP-DASH-2', 'name' => 'Critical', 'unit_of_measure' => 'pcs',
            'current_quantity' => 5, 'safety_stock_threshold' => 20, 'is_active' => true,
        ]);
        Component::create([
            'code' => 'CMP-DASH-3', 'name' => 'Out', 'unit_of_measure' => 'pcs',
            'current_quantity' => 0, 'safety_stock_threshold' => 10, 'is_active' => true,
        ]);
        Component::create([
            'code' => 'CMP-DASH-4', 'name' => 'Fine', 'unit_of_measure' => 'pcs',
            'current_quantity' => 100, 'safety_stock_threshold' => 10, 'is_active' => true,
        ]);

        $response = $this->actingAs($sm)->get(route('stock.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('total_components', 3);
        $response->assertViewHas('critical_count', 1);
        $response->assertViewHas('out_of_stock_count', 1);
    }

    public function test_production_dashboard_shows_planned_vs_produced(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $product = Product::create(['code' => 'PRD-DASH-3', 'name' => 'Prod Dash Product']);
        $line = ProductionLine::create(['code' => 'LINE-DASH-3', 'name' => 'Prod Dash Line']);
        $order = ProductionOrder::create([
            'order_number' => 'PO-DASH-0003',
            'product_id' => $product->id,
            'production_line_id' => $line->id,
            'planned_quantity' => 100,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'created_by' => $pm->id,
        ]);
        ProductionRecord::create([
            'production_order_id' => $order->id,
            'produced_quantity' => 40,
            'recorded_by' => $pm->id,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($pm)->get(route('production.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('planned_quantity_total', 100);
        $response->assertViewHas('produced_quantity_total', 40);
    }

    public function test_dashboards_reject_the_wrong_role(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->get(route('quality.dashboard'))->assertStatus(403);
        $this->actingAs($sm)->get(route('production.dashboard'))->assertStatus(403);
        $this->actingAs($sm)->get(route('dashboard'))->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_reach_any_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('production.dashboard'))->assertRedirect(route('login'));
        $this->get(route('quality.dashboard'))->assertRedirect(route('login'));
        $this->get(route('stock.dashboard'))->assertRedirect(route('login'));
    }
}
