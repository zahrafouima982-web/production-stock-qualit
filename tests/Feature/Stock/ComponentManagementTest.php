<?php

namespace Tests\Feature\Stock;

use App\Models\Component;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponentManagementTest extends TestCase
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

    public function test_new_component_always_starts_at_zero_quantity(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->post(route('components.store'), [
            'code' => 'CMP-001',
            'name' => 'Test Connector',
            'unit_of_measure' => 'pcs',
            'safety_stock_threshold' => 50,
            // deliberately trying to smuggle a starting quantity in
            'current_quantity' => 999,
        ]);

        $component = Component::firstWhere('code', 'CMP-001');

        $this->assertNotNull($component);
        $this->assertEquals(0, $component->current_quantity, 'current_quantity must never be settable from the form.');
    }

    public function test_stock_manager_can_create_update_and_delete_components(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->post(route('components.store'), [
            'code' => 'CMP-002',
            'name' => 'Cable Reel',
            'unit_of_measure' => 'meters',
            'safety_stock_threshold' => 100,
        ])->assertRedirect(route('components.index'));

        $component = Component::firstWhere('code', 'CMP-002');

        $this->actingAs($sm)->put(route('components.update', $component), [
            'code' => 'CMP-002',
            'name' => 'Cable Reel V2',
            'unit_of_measure' => 'meters',
            'safety_stock_threshold' => 150,
        ])->assertRedirect(route('components.index'));

        $this->actingAs($sm)->delete(route('components.destroy', $component))
            ->assertRedirect(route('components.index'));

        $this->assertSoftDeleted($component);
    }

    public function test_admin_can_also_manage_components(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)->post(route('components.store'), [
            'code' => 'CMP-003',
            'name' => 'Terminal',
            'unit_of_measure' => 'pcs',
            'safety_stock_threshold' => 20,
        ])->assertRedirect(route('components.index'));

        $this->assertNotNull(Component::firstWhere('code', 'CMP-003'));
    }

    public function test_production_manager_can_view_but_not_create_components(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $this->actingAs($pm)->get(route('components.index'))->assertStatus(200);

        $this->actingAs($pm)->post(route('components.store'), [
            'code' => 'CMP-004',
            'name' => 'Blocked',
            'unit_of_measure' => 'pcs',
            'safety_stock_threshold' => 10,
        ])->assertStatus(403);
    }

    public function test_quality_controller_has_no_access_to_components(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->get(route('components.index'))->assertStatus(403);
    }
}
