<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Test-only factory. Real roles come from RoleSeeder (fixed set of 4);
 * this exists purely so User::factory() has a valid role_id to attach to
 * in tests without hardcoding one of the 4 production role names.
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => strtoupper($this->faker->unique()->word()),
            'description' => $this->faker->sentence(),
        ];
    }
}
