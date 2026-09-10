<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'description' => 'Full system oversight: users, configuration, global supervision.',
            ],
            [
                'name' => Role::PRODUCTION_MANAGER,
                'description' => 'Manages production lines, products, orders, and production records.',
            ],
            [
                'name' => Role::QUALITY_CONTROLLER,
                'description' => 'Records inspections, defects, causes, and corrective actions.',
            ],
            [
                'name' => Role::STOCK_MANAGER,
                'description' => 'Manages components, stock movements, safety stock, and alerts.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
