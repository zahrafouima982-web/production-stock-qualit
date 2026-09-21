<?php

namespace Tests\Feature\Stock;

use App\Models\Component;
use App\Models\Role;
use App\Models\StockAlert;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAlertTest extends TestCase
{
    use RefreshDatabase;

    protected User $sm;
    protected StockAlert $alert;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->sm = User::factory()->create(['role_id' => Role::where('name', Role::STOCK_MANAGER)->value('id')]);

        $component = Component::create([
            'code' => 'CMP-ALT-1',
            'name' => 'Alert Test Component',
            'unit_of_measure' => 'pcs',
            'current_quantity' => 5,
            'safety_stock_threshold' => 20,
            'is_active' => true,
        ]);

        $this->alert = StockAlert::create([
            'component_id' => $component->id,
            'status' => StockAlert::STATUS_ACTIVE,
            'triggered_at' => now(),
        ]);
    }

    private function userWithRole(string $roleName): User
    {
        return User::factory()->create([
            'role_id' => Role::where('name', $roleName)->value('id'),
        ]);
    }

    public function test_stock_manager_can_manually_resolve_an_active_alert(): void
    {
        $response = $this->actingAs($this->sm)->post(route('stock.alerts.resolve', $this->alert));

        $this->alert->refresh();

        $this->assertSame(StockAlert::STATUS_RESOLVED, $this->alert->status);
        $this->assertSame($this->sm->id, $this->alert->resolved_by, 'Manual resolution must attribute resolved_by to the acting user.');
        $this->assertNotNull($this->alert->resolved_at);
        $response->assertRedirect(route('stock.alerts.index'));
    }

    public function test_an_already_resolved_alert_cannot_be_resolved_again(): void
    {
        $this->alert->update(['status' => StockAlert::STATUS_RESOLVED, 'resolved_at' => now()]);

        $this->actingAs($this->sm)->post(route('stock.alerts.resolve', $this->alert))
            ->assertSessionHasErrors('alert');

        $this->assertSame(StockAlert::STATUS_RESOLVED, $this->alert->fresh()->status);
    }

    public function test_admin_can_view_alerts_but_not_resolve_them(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)->get(route('stock.alerts.index'))->assertStatus(200);
        $this->actingAs($admin)->post(route('stock.alerts.resolve', $this->alert))->assertStatus(403);
    }

    public function test_production_manager_and_quality_controller_have_no_access_to_alerts(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($pm)->get(route('stock.alerts.index'))->assertStatus(403);
        $this->actingAs($qc)->get(route('stock.alerts.index'))->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_stock_alerts(): void
    {
        $this->get(route('stock.alerts.index'))->assertRedirect(route('login'));
    }
}
