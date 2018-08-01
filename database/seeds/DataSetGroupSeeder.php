<?php

use App\DataSet;
use App\DataSetGroup;
use App\Organisation;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DataSetGroupSeeder extends Seeder
{
    const DATA_SET_GROUP_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $dataSets = DataSet::select('id')->limit(self::DATA_SET_GROUP_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::DATA_SET_GROUP_RECORDS)->get()->toArray();

        foreach (range(1, self::DATA_SET_GROUP_RECORDS) as $index) {
            $dataSet =  $this->faker->unique()->randomElement($dataSets)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];

            DataSetGroup::create([
                'data_set_id' => $dataSet,
                'group_id'  => $organisation
            ]);
        }
    }
}
