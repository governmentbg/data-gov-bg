<?php

use App\DataSet;
use App\Organisation;
use App\CustomSetting;
use App\Resource;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CustomSettingSeeder extends Seeder
{
    const CUSTOM_SETTING_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $organisations = Organisation::select('id')->limit(self::CUSTOM_SETTING_RECORDS)->get()->toArray();

        foreach (range(1, self::CUSTOM_SETTING_RECORDS) as $index) {
            $organisation = $this->faker->randomElement($organisations)['id'];

            CustomSetting::create([
                'org_id' => $organisation,
                'data_set_id'  => null,
                'resource_id' =>  null,
                'key' => $this->faker->randomDigit(),
                'value' => $this->faker->randomDigit()
            ]);
        }
    }
}
