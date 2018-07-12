<?php

use App\Signal;
use App\Resource;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SignalSeeder extends Seeder
{
    const SIGNAL_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $resources = Resource::limit(self::SIGNAL_RECORDS)->get()->toArray();
        foreach (range(1, self::SIGNAL_RECORDS) as $i) {
            $resource = $this->faker->randomElement($resources)['id'];
            Signal::create([
                'resource_id' => $resource,
                'descript' =>$this->faker->sentence(4),
                'firstname' => $this->faker->firstName(),
                'lastname' => $this->faker->lastName(),
                'email'=> $this->faker->email(),
                'status' => $this->faker->boolean()
            ]);
        }
    }
}
