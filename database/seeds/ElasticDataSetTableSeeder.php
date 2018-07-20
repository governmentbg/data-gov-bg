<?php

use App\DataSet;
use App\ElasticDataSet;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ElasticDataSetTableSeeder extends Seeder
{
    const ELASTIC_DATA_SET_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::ELASTIC_DATA_SET_RECORDS) as $index) {

            ElasticDataSet::create([
                'index' => $this->faker->word,
                'index_type'  => $this->faker->word,
                'doc' => $this->faker->randomDigit()
            ]);
        }
    }
}
