<?php

namespace Tests\Feature\Production;

use App\Models\ProductionLine;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * This exact module (production-lines) shipped twice with a broken
 * authorizeResource() call that no test caught — the bug only surfaced
 * when someone clicked through the UI. This file exists specifically to
 * close that gap: every route actually gets hit here.
 */
class ProductionLineManagementTest extends TestCase
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

    public function test_the_index_page_renders(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)->get(route('production-lines.index'))->assertStatus(200);
    }

    public function test_admin_can_create_update_and_delete_production_lines(): void
    {
        $admin = $this->userWithRole(Role::ADMIN);

        $this->actingAs($admin)
            ->post(route('production-lines.store'), ['code' => 'LINE-T1', 'name' => 'Test Line'])
            ->assertRedirect(route('production-lines.index'));

        $line = ProductionLine::firstWhere('code', 'LINE-T1');
        $this->assertNotNull($line);

        $this->actingAs($admin)
            ->put(route('production-lines.update', $line), ['code' => 'LINE-T1', 'name' => 'Updated Line'])
            ->assertRedirect(route('production-lines.index'));

        $this->actingAs($admin)
            ->delete(route('production-lines.destroy', $line))
            ->assertRedirect(route('production-lines.index'));

        $this->assertSoftDeleted($line);
    }

    public function test_production_manager_can_create_and_update_but_not_delete_production_lines(): void
    {
        $pm = $this->userWithRole(Role::PRODUCTION_MANAGER);
        $line = ProductionLine::create(['code' => 'LINE-T2', 'name' => 'PM Line']);

        $this->actingAs($pm)
            ->put(route('production-lines.update', $line), ['code' => 'LINE-T2', 'name' => 'PM Line V2'])
            ->assertRedirect(route('production-lines.index'));

        $this->actingAs($pm)
            ->delete(route('production-lines.destroy', $line))
            ->assertStatus(403);
    }

    public function test_quality_controller_can_view_but_not_create_production_lines(): void
    {
        $qc = $this->userWithRole(Role::QUALITY_CONTROLLER);

        $this->actingAs($qc)->get(route('production-lines.index'))->assertStatus(200);

        $this->actingAs($qc)
            ->post(route('production-lines.store'), ['code' => 'LINE-T3', 'name' => 'Blocked Line'])
            ->assertStatus(403);
    }

    public function test_stock_manager_has_no_access_to_production_lines(): void
    {
        $sm = $this->userWithRole(Role::STOCK_MANAGER);

        $this->actingAs($sm)->get(route('production-lines.index'))->assertStatus(403);
    }
}
