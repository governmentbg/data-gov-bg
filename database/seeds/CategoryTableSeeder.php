<?php

use App\Category;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1,3) as $index) {
            Category::create([
                'name'              => $faker->name,
                'icon_file_name'    => $faker->name,
                'icon_mime_type'    => $faker->mimeType,
                'icon_data'         => $faker->name,
                'active'            => 1,
                'ordering'          => 1,
	        ]);
	    }
    }
}
