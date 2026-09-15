<?php

namespace Tests\Feature\Production;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_admin_can_create_update_and_delete_products(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)
            ->post(route('products.store'), ['code' => 'PRD-001', 'name' => 'Test Harness'])
            ->assertRedirect(route('products.index'));

        $product = Product::firstWhere('code', 'PRD-001');
        $this->assertNotNull($product);

        $this->actingAs($admin)
            ->put(route('products.update', $product), ['code' => 'PRD-001', 'name' => 'Updated Harness'])
            ->assertRedirect(route('products.index'));

        $this->actingAs($admin)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertSoftDeleted($product);
    }

    public function test_production_manager_can_create_and_update_but_not_delete_products(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $product = Product::create(['code' => 'PRD-002', 'name' => 'Cable Set']);

        $this->actingAs($pm)
            ->put(route('products.update', $product), ['code' => 'PRD-002', 'name' => 'Cable Set V2'])
            ->assertRedirect(route('products.index'));

        $this->actingAs($pm)
            ->delete(route('products.destroy', $product))
            ->assertStatus(403);
    }

    public function test_quality_controller_can_view_but_not_create_products(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->get(route('products.index'))->assertStatus(200);

        $this->actingAs($qc)
            ->post(route('products.store'), ['code' => 'PRD-003', 'name' => 'Blocked Product'])
            ->assertStatus(403);
    }

    public function test_stock_manager_has_no_access_to_products(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->get(route('products.index'))->assertStatus(403);
    }
}
