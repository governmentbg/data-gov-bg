<?php

use App\DataSet;
use App\Resource;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    const RESOURCE_COUNT = 3;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $types = array_keys(Resource::getTypes());
        $files = array_keys(Resource::getFormats());
        $httpTypes = array_keys(Resource::getRequestTypes());
        $dataSets = DataSet::orderBy('created_at', 'desc')->limit(self::RESOURCE_COUNT)->get()->toArray();

        foreach ($dataSets as $set) {
            foreach (range(1, self::RESOURCE_COUNT) as $index) {
                $type = $this->faker->randomElement($types);
                $fileType = $this->faker->randomElement($files);
                $httpType = $this->faker->randomElement($httpTypes);

                Resource::create([
                    'data_set_id'       => $set['id'],
                    'uri'               => $this->faker->uuid(),
                    'version'           => $this->faker->unique()->word,
                    'resource_type'     => $type,
                    'file_format'       => $fileType,
                    'resource_url'      => $this->faker->name(),
                    'http_rq_type'      => $httpType,
                    'authentication'    => $this->faker->name(),
                    'post_data'         => $this->faker->name(),
                    'http_headers'      => $this->faker->text(),
                    'name'              => [
                        'en' => $this->faker->name(),
                    ],
                    'descript'          => [
                        'en' => $this->faker->text(),
                    ],
                    'schema_descript'   => $this->faker->text(),
                    'schema_url'        => $this->faker->name(),
                    'is_reported'       => $this->faker->boolean(),
                ]);
            }
        }
    }
}
