<?php

use App\DataSet;
use App\Category;
use App\DataSetSubCategory;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DataSetSubCategorySeeder extends Seeder
{
    const DATA_SET_SUB_CATEGORIES_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $dataSets = DataSet::select('id')->limit(self::DATA_SET_SUB_CATEGORIES_RECORDS)->get()->toArray();
        $categories = Category::select('id')->limit(self::DATA_SET_SUB_CATEGORIES_RECORDS)->get()->toArray();

        foreach (range(1, self::DATA_SET_SUB_CATEGORIES_RECORDS) as $index) {
            $dataSet =  $this->faker->unique()->randomElement($dataSets)['id'];
            $category = $this->faker->randomElement($categories)['id'];

            DataSetSubCategory::create([
                'data_set_id' => $dataSet,
                'sub_cat_id'  => $category
            ]);
        }
    }
}
