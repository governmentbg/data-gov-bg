<?php

use App\Locale;
use App\DataSet;
use App\Resource;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    const RESOURCE_RECORDS = 100;

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
        $dataSets = DataSet::orderBy('created_at', 'desc')->limit(self::RESOURCE_RECORDS)->get()->toArray();
        $locales = Locale::where('active', 1)->limit(self::RESOURCE_RECORDS)->get()->toArray();

        foreach (range(1, self::RESOURCE_RECORDS) as $i) {
            $dataSet = $this->faker->randomElement($dataSets)['id'];
            $locale = $this->faker->randomElement($locales)['locale'];
            $type = $this->faker->randomElement($types);
            $fileType = $this->faker->randomElement($files);
            $httpType = $this->faker->randomElement($httpTypes);

            \LaravelLocalization::setLocale($locale);

            Resource::create([
                'data_set_id'       => $dataSet,
                'uri'               => $this->faker->uuid(),
                'version'           => 1,
                'resource_type'     => $type,
                'file_format'       => $fileType,
                'resource_url'      => $this->faker->name(),
                'http_rq_type'      => $httpType,
                'authentication'    => $this->faker->name(),
                'post_data'         => $this->faker->name(),
                'http_headers'      => $this->faker->text(),
                'name'              => $this->faker->name(),
                'descript'          => $this->faker->text(),
                'schema_descript'   => $this->faker->text(),
                'schema_url'        => $this->faker->name(),
                'is_reported'       => $this->faker->boolean(),
            ])->searchable();
        }
    }
}
