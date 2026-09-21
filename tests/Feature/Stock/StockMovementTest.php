<?php

namespace Tests\Feature\Stock;

use App\Models\Component;
use App\Models\Role;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    protected User $sm;
    protected Component $component;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->sm = User::factory()->create(['role_id' => Role::where('name', Role::STOCK_MANAGER)->value('id')]);

        $this->component = Component::create([
            'code' => 'CMP-STK-1',
            'name' => 'Test Component',
            'unit_of_measure' => 'pcs',
            'current_quantity' => 100,
            'safety_stock_threshold' => 30,
            'is_active' => true,
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    public function test_an_in_movement_increases_current_quantity(): void
    {
        $response = $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'IN',
            'quantity' => 50,
        ]);

        $this->component->refresh();

        $this->assertEquals(150, $this->component->current_quantity);
        $this->assertSame(1, StockMovement::count());
        $response->assertRedirect(route('stock.movements.index'));
    }

    public function test_an_out_movement_decreases_current_quantity(): void
    {
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 40,
        ]);

        $this->component->refresh();

        $this->assertEquals(60, $this->component->current_quantity);
    }

    public function test_an_out_movement_greater_than_available_stock_is_rejected(): void
    {
        $response = $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 999,
        ]);

        $response->assertSessionHasErrors('quantity');

        $this->component->refresh();
        $this->assertEquals(100, $this->component->current_quantity, 'Quantity must be unchanged after a rejected OUT.');
        $this->assertSame(0, StockMovement::count());
    }

    public function test_two_sequential_out_movements_cannot_together_overdraw_stock(): void
    {
        // First OUT takes it right down to the edge (100 -> 10).
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 90,
        ]);

        $this->component->refresh();
        $this->assertEquals(10, $this->component->current_quantity);

        // Second OUT tries to take more than what's left — must be rejected,
        // proving the check re-reads the current balance rather than trusting
        // a stale value from before the first movement.
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 20,
        ])->assertSessionHasErrors('quantity');

        $this->component->refresh();
        $this->assertEquals(10, $this->component->current_quantity);
        $this->assertSame(1, StockMovement::count(), 'Only the first, valid movement should exist.');
    }

    public function test_an_out_movement_crossing_the_threshold_triggers_an_alert(): void
    {
        // Threshold is 30; dropping to 20 should trigger ACTIVE alert.
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 80,
        ]);

        $alert = StockAlert::where('component_id', $this->component->id)->first();

        $this->assertNotNull($alert);
        $this->assertSame(StockAlert::STATUS_ACTIVE, $alert->status);
    }

    public function test_a_subsequent_in_movement_auto_resolves_the_alert(): void
    {
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 80,
        ]);
        $this->assertSame(StockAlert::STATUS_ACTIVE, StockAlert::first()->status);

        // Bring it back above threshold (20 -> 60).
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'IN',
            'quantity' => 40,
        ]);

        $alert = StockAlert::first()->fresh();
        $this->assertSame(StockAlert::STATUS_RESOLVED, $alert->status);
        $this->assertNull($alert->resolved_by, 'Auto-resolution must not attribute resolved_by to a user.');
    }

    public function test_a_duplicate_active_alert_is_never_created(): void
    {
        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 80, // 100 -> 20, triggers alert
        ]);

        $this->actingAs($this->sm)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'OUT',
            'quantity' => 5, // 20 -> 15, still below threshold
        ]);

        $this->assertSame(1, StockAlert::where('component_id', $this->component->id)->count());
    }

    public function test_admin_can_view_movements_but_not_record_them(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)->get(route('stock.movements.index'))->assertStatus(200);

        $this->actingAs($admin)->post(route('stock.movements.store'), [
            'component_id' => $this->component->id,
            'type' => 'IN',
            'quantity' => 10,
        ])->assertStatus(403);
    }

    public function test_production_manager_and_quality_controller_have_no_access_to_movements(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($pm)->get(route('stock.movements.index'))->assertStatus(403);
        $this->actingAs($qc)->get(route('stock.movements.index'))->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_stock_movements(): void
    {
        $this->get(route('stock.movements.index'))->assertRedirect(route('login'));
    }
}
