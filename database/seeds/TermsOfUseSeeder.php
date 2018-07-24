<?php

use App\TermsOfUse;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TermsOfUseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1,3) as $index) {
            TermsOfUse::create([
                'name'          => $faker->name,
                'descript'      => $faker->text,
                'active'        => 1,
                'is_default'    => 1,
                'ordering'      => 1,
            ]);
        }
    }
}

