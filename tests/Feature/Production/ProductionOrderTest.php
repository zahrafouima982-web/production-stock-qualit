<?php

namespace Tests\Feature\Production;

use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;
    protected ProductionLine $line;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->product = Product::create(['code' => 'PRD-100', 'name' => 'Engine Harness']);
        $this->line = ProductionLine::create(['code' => 'LINE-1', 'name' => 'Line 1']);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    public function test_production_manager_can_create_an_order(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $response = $this->actingAs($pm)->post(route('production.orders.store'), [
            'product_id' => $this->product->id,
            'production_line_id' => $this->line->id,
            'planned_quantity' => 100,
        ]);

        $order = ProductionOrder::first();

        $this->assertNotNull($order);
        $this->assertSame(ProductionOrder::STATUS_PLANNED, $order->status);
        $this->assertSame($pm->id, $order->created_by);
        $response->assertRedirect(route('production.orders.show', $order));
    }

    public function test_admin_cannot_create_an_order_but_can_view_it(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)
            ->post(route('production.orders.store'), [
                'product_id' => $this->product->id,
                'production_line_id' => $this->line->id,
                'planned_quantity' => 50,
            ])
            ->assertStatus(403);

        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $order = $this->createOrder($pm, 50);

        $this->actingAs($admin)->get(route('production.orders.show', $order))->assertStatus(200);
    }

    public function test_quality_controller_can_view_orders_but_not_create_them(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->get(route('production.orders.index'))->assertStatus(200);

        $this->actingAs($qc)
            ->post(route('production.orders.store'), [
                'product_id' => $this->product->id,
                'production_line_id' => $this->line->id,
                'planned_quantity' => 50,
            ])
            ->assertStatus(403);
    }

    public function test_stock_manager_has_no_access_to_production_orders(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->get(route('production.orders.index'))->assertStatus(403);
    }

    public function test_recording_production_moves_order_to_in_progress_then_completed(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $order = $this->createOrder($pm, 100);

        $this->actingAs($pm)->post(route('production.orders.records.store', $order), [
            'produced_quantity' => 40,
        ]);

        $order->refresh();
        $this->assertSame(ProductionOrder::STATUS_IN_PROGRESS, $order->status);

        $this->actingAs($pm)->post(route('production.orders.records.store', $order), [
            'produced_quantity' => 60,
        ]);

        $order->refresh();
        $this->assertSame(ProductionOrder::STATUS_COMPLETED, $order->status);
        $this->assertNotNull($order->end_date);
    }

    public function test_a_completed_order_cannot_be_cancelled(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $order = $this->createOrder($pm, 10);

        $this->actingAs($pm)->post(route('production.orders.records.store', $order), [
            'produced_quantity' => 10,
        ]);

        $order->refresh();
        $this->assertSame(ProductionOrder::STATUS_COMPLETED, $order->status);

        $this->actingAs($pm)->post(route('production.orders.cancel', $order))->assertStatus(403);
    }

    public function test_a_planned_order_can_be_cancelled(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $order = $this->createOrder($pm, 10);

        $this->actingAs($pm)->post(route('production.orders.cancel', $order))
            ->assertRedirect(route('production.orders.show', $order));

        $order->refresh();
        $this->assertSame(ProductionOrder::STATUS_CANCELLED, $order->status);
    }

    private function createOrder(User $pm, int $plannedQuantity): ProductionOrder
    {
        $this->actingAs($pm)->post(route('production.orders.store'), [
            'product_id' => $this->product->id,
            'production_line_id' => $this->line->id,
            'planned_quantity' => $plannedQuantity,
        ]);

        return ProductionOrder::latest()->first();
    }
}
