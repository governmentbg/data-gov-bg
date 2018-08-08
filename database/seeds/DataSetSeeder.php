<?php

use App\Locale;
use App\DataSet;
use App\Organisation;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Http\Controllers\ApiController;

class DataSetSeeder extends Seeder
{
    const DATA_SET_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $statuses = array_keys(DataSet::getStatus());
        $visibilities = array_keys(DataSet::getVisibility());
        $organisations = Organisation::select('id')->limit(self::DATA_SET_RECORDS)->get()->toArray();
        $locales = Locale::where('active', 1)->limit(self::DATA_SET_RECORDS)->get()->toArray();

        foreach (range(1, self::DATA_SET_RECORDS) as $index) {
            $status = $this->faker->randomElement($statuses);
            $visibility = $this->faker->randomElement($visibilities);
            $organisation = $this->faker->randomElement($organisations)['id'];
            $locale = $this->faker->randomElement($locales)['locale'];

            DataSet::create([
                'org_id'        => $organisation,
                'uri'           => $this->faker->uuid(),
                'name'          => ApiController::trans($locale, $this->faker->word()),
                'descript'      => ApiController::trans($locale, $this->faker->text()),
                'author_name'   => $this->faker->name(),
                'author_email'  => $this->faker->email(),
                'support_name'  => $this->faker->name(),
                'support_email' => $this->faker->email(),
                'visibility'    => $visibility,
                'version'       => 1,
                'status'        => $status,
            ])->searchable();
        }
    }
}
