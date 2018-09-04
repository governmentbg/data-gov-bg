<?php

use App\Category;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Http\Controllers\ApiController;

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
        $locale = App::getLocale();

        foreach (range(1, self::CATEGORY_RECORDS) as $i) {
            $category = Category::create([
                'name'              => ApiController::trans($locale, $this->faker->name),
                'icon_file_name'    => $this->faker->name,
                'icon_mime_type'    => $this->faker->mimeType,
                'icon_data'         => $this->faker->name,
                'parent_id'         => $i < 5 || empty($category) ? null : $category->id,
                'active'            => $this->faker->boolean(),
                'ordering'          => $this->faker->randomDigit(),
            ]);
        }
    }
}
