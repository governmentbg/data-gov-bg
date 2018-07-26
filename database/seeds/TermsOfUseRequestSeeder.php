<?php

use App\TermsOfUseRequest;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TermsOfUseRequestSeeder extends Seeder
{
    const TERMS_OF_USE_REQUEST_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::TERMS_OF_USE_REQUEST_RECORDS) as $index) {
            TermsOfUseRequest::create([
                'descript'      => $this->faker->sentence(),
                'firstname'     => $this->faker->firstName(),
                'lastname'      => $this->faker->lastName(),
                'email'         => $this->faker->email(),
                'status'        => $this->faker->boolean()
            ])->searchable();
        }
    }
}
