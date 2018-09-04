<?php

use App\Tags;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TagsSeeder extends Seeder
{
    const TAGS_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::TAGS_RECORDS) as $i) {
            $dbData = ['name' => $this->faker->unique()->word];

            try {
                $record = Tags::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }
        }
    }
}
