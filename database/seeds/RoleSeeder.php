<?php

use App\Role;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    const ROLE_RECORDS = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::ROLE_RECORDS) as $i) {
            Role::create([
                'name'      => $this->faker->word,
                'active'    => $this->faker->boolean(),
            ]);
        }
    }
}
