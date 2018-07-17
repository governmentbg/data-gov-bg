<?php

use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    const USER_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::USER_RECORDS) as $i) {
            $username = $this->faker->unique()->name;

            User::create([
                'username'  => $username,
                'password'  => bcrypt($username),
                'email'     => $this->faker->unique()->email,
                'firstname' => $this->faker->firstName(),
                'lastname'  => $this->faker->lastName(),
                'add_info'  => $i != 1 ? $this->faker->text(intval(8000 / $i)) : null,
                'is_admin'  => rand(0, 1),
                'active'    => rand(0, 1),
                'approved'  => rand(0, 1),
                'api_key'   => $this->faker->uuid(),
                'hash_id'   => $this->faker->md5(),
            ]);
        }
    }
}
