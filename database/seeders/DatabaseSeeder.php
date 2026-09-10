<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        // One guaranteed login per role, always created — independent of demo data.
        User::updateOrCreate(
            ['email' => 'admin@pqtms.test'],
            [
                'name' => 'System Administrator',
                'password' => 'password', // hashed automatically via the 'hashed' cast
                'role_id' => Role::where('name', Role::ADMIN)->value('id'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'production@pqtms.test'],
            [
                'name' => 'Production Manager',
                'password' => 'password',
                'role_id' => Role::where('name', Role::PRODUCTION_MANAGER)->value('id'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'quality@pqtms.test'],
            [
                'name' => 'Quality Controller',
                'password' => 'password',
                'role_id' => Role::where('name', Role::QUALITY_CONTROLLER)->value('id'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'stock@pqtms.test'],
            [
                'name' => 'Stock Manager',
                'password' => 'password',
                'role_id' => Role::where('name', Role::STOCK_MANAGER)->value('id'),
                'is_active' => true,
            ]
        );

        // Optional richer demo dataset (products, lines, components, a full
        // production -> quality -> stock workflow). Comment out the line below
        // if you only want the four base users for now.
        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
