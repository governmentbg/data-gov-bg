<?php

use App\Tags;
use App\DataSet;
use App\DataSetTags;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DataSetTagsSeeder extends Seeder
{
    const DATA_SET_TAGS_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $dataSets = DataSet::select('id')->limit(self::DATA_SET_TAGS_RECORDS)->get()->toArray();
        $tags = Tags::select('id')->limit(self::DATA_SET_TAGS_RECORDS)->get()->toArray();

        foreach (range(1, self::DATA_SET_TAGS_RECORDS) as $i) {
            $dataSet = $this->faker->randomElement($dataSets)['id'];
            $tag = $this->faker->randomElement($tags)['id'];

            $record = null;
            $dbData = [
                'data_set_id'   => $dataSet,
                'tag_id'        => $tag
            ];

            if (!DataSetTags::where($dbData)->count()) {
                try {
                    $record = DataSetTags::create($dbData);
                } catch (QueryException $ex) {
                    $this->log($ex->getMessage());
                }
            }
        }
    }
}
