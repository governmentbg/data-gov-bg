<?php

use App\DataSet;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

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

        foreach (range(1, self::DATA_SET_RECORDS) as $index) {
            $status = $this->faker->randomElement($statuses);
            $visibility = $this->faker->randomElement($visibilities);

            DataSet::create([
                'uri'           => $this->faker->uuid(),
                'name'          => $this->faker->word(),
                'descript'      => $this->faker->text(),
                'author_name'   => $this->faker->name(),
                'author_email'  => $this->faker->email(),
                'support_name'  => $this->faker->name(),
                'support_email' => $this->faker->email(),
                'visibility'    => $visibility,
                'version'       => 1,
                'status'        => $status,
            ]);
        }
    }
}
