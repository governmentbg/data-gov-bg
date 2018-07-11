<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PasswordResetSeeder extends Seeder
{
    const PASSWORD_RESET_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::PASSWORD_RESET_RECORDS) as $i) {
            DB::table('password_resets')->insert([
                'email'         => $this->faker->unique()->email,
                'token'         => $this->faker->uuid(),
                'created_at'    => $i != 1 ? $this->faker->dateTime() : null,
            ]);
        }
    }
}
