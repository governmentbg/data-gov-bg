<?php

use App\Resource;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ResourceTableSeeder extends Seeder
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
            Resource::create([
                'data_set_id'       => 16,
                'uri'               => $faker->uuid,
                'version'           => $index,
                'resource_type'     => 1,
                'file_format'       => 1,
                'resource_url'      => $faker->name,
                'http_rq_type'      => 1,
                'authentication'    => $faker->name,
                'post_data'         => $faker->name,
                'http_headers'      => $faker->text,
                'name'              => [
                    'en' => $faker->name,
                ],
                'descript'          => [
                    'en' => $faker->text,
                ],
                'schema_descript'   => $faker->text,
                'schema_url'        => $faker->name,
                'is_reported'       => 1,
            ]);
        }
    }
}
