<?php

use App\Locale;
use App\Resource;
use App\DataSet;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    const RESOURCE_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $locales = Locale::where('active', 1)->limit(self::RESOURCE_RECORDS)->get()->toArray();
        $dataSets = DataSet::limit(self::RESOURCE_RECORDS)->get()->toArray();

        foreach (range(1, self::RESOURCE_RECORDS) as $i) {
            $locale = $this->faker->randomElement($locales)['locale'];
            $dataSet = $this->faker->randomElement($dataSets)['id'];

            Resource::create([
                'data_set_id'       => $dataSet,
                'uri'               => $this->faker->uuid,
                'version'           => $i,
                'resource_type'     => 1,
                'file_format'       => 1,
                'resource_url'      => $this->faker->name,
                'http_rq_type'      => 1,
                'authentication'    => $this->faker->name,
                'post_data'         => $this->faker->name,
                'http_headers'      => $this->faker->text,
                'name'              => [$locale => $this->faker->name],
                'descript'          => [$locale => $this->faker->name],
                'schema_descript'   => $this->faker->text,
                'schema_url'        => $this->faker->name,
                'is_reported'       => 1,
            ]);
        }
    }
}