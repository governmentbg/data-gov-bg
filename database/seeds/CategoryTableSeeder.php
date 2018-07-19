<?php

use App\Category;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    const CATEGORY_COUNT = 5;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, self::CATEGORY_COUNT) as $index) {
            Category::create([
                'name'              => $faker->name,
                'icon_file_name'    => $faker->name,
                'icon_mime_type'    => $faker->mimeType,
                'icon_data'         => $faker->name,
                'active'            => true,
                'ordering'          => Category::ORDERING_ASC,
            ]);
        }
    }
}
