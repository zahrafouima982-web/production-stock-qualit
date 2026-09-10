<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
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
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_every_protected_module(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/production/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/quality/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/stock/dashboard')->assertStatus(200);
    }

    public function test_production_manager_cannot_access_quality_stock_or_admin(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);

        $this->actingAs($pm)->get('/production/dashboard')->assertStatus(200);
        $this->actingAs($pm)->get('/quality/dashboard')->assertStatus(403);
        $this->actingAs($pm)->get('/stock/dashboard')->assertStatus(403);
        $this->actingAs($pm)->get('/dashboard')->assertStatus(403);
    }

    public function test_quality_controller_cannot_access_stock_or_admin(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->get('/quality/dashboard')->assertStatus(200);
        $this->actingAs($qc)->get('/stock/dashboard')->assertStatus(403);
        $this->actingAs($qc)->get('/dashboard')->assertStatus(403);
    }

    public function test_stock_manager_cannot_access_quality_or_admin(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->get('/stock/dashboard')->assertStatus(200);
        $this->actingAs($sm)->get('/quality/dashboard')->assertStatus(403);
        $this->actingAs($sm)->get('/dashboard')->assertStatus(403);
    }

    public function test_deactivated_user_is_denied_even_with_a_valid_role(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);
        $admin->update(['is_active' => false]);

        $this->actingAs($admin)->get('/dashboard')->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_from_every_protected_area(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/production/dashboard')->assertRedirect('/login');
        $this->get('/quality/dashboard')->assertRedirect('/login');
        $this->get('/stock/dashboard')->assertRedirect('/login');
    }
}
