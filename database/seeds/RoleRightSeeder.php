<?php

use App\Role;
use App\RoleRight;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class RoleRightSeeder extends Seeder
{
    const ROLE_RIGHT_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $rights = array_keys(RoleRight::getRights());
        $roles = Role::orderBy('created_at', 'desc')->limit(self::ROLE_RIGHT_RECORDS)->get()->toArray();

        // Test creation
        foreach ($roles as $role) {
            foreach ($rights as $right) {
                RoleRight::create([
                    'role_id'           => $role['id'],
                    'module_name'       => $this->faker->unique()->word,
                    'right'             => $right,
                    'limit_to_own_data' => $this->faker->boolean(),
                    'api'               => $this->faker->boolean(),
                ]);
            }
        }
    }
}
