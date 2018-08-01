<?php

use App\Category;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    const CATEGORY_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        foreach (range(1, self::CATEGORY_RECORDS) as $i) {
            Category::create([
                'name'              => $this->faker->name,
                'icon_file_name'    => $this->faker->name,
                'icon_mime_type'    => $this->faker->mimeType,
                'icon_data'         => $this->faker->name,
                'active'            => $this->faker->boolean(),
                'ordering'          => $this->faker->randomDigit(),
            ]);
        }
    }
}
