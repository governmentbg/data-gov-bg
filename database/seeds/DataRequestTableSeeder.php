<?php

use App\DataRequest;
use App\Organisation;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DataRequestSeeder extends Seeder
{
    const DATAREQUEST_RECORDS = 10;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $orgs = Organisation::orderBy('created_at', 'desc')->limit(self::DATAREQUEST_RECORDS)->get()->toArray();

        foreach (range(1, self::DATAREQUEST_RECORDS) as $i) {
            $org = $this->faker->randomElement($orgs)['id'];
            DataRequest::create([
                'org_id' => $org,
                'descript' => $this->faker->sentence(3),
                'published_url' => $this->faker->url,
                'contact_name' => $this->faker->name,
                'email' => $this->faker->email,
                'notes' => $this->faker->sentence(4),
                'status' => $this->faker->boolean(),
            ]);
        }
    }
}
