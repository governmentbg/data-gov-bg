<?php

use App\Category;
use App\DataSet;
use App\Organisation;
use App\TermsOfUse;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class DataSetsSeeder extends Seeder
{
    const DATASET_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $organisations = Organisation::limit(self::DATASET_RECORDS)->get()->toArray();
        $categories = Category::limit(self::DATASET_RECORDS)->get()->toArray();
        $termsOfUses = TermsOfUse::limit(self::DATASET_RECORDS)->get()->toArray();
        foreach (range(1, self::DATASET_RECORDS) as $i) {
            $organisation = $this->faker->randomElement($organisations)['id'];
            $category = $this->faker->randomElement($categories)['id'];
            $termsOfUse = $this->faker->randomElement($termsOfUses)['id'];
            DataSet::create([
                'org_id' => $organisation,
                'uri' => $this->faker->uuid,
                'name' => $this->faker->name,
                'descript' => $this->faker->sentence(5),
                'category_id' => $category,
                'terms_of_use_id' => $termsOfUse,
                'visibility' => $this->faker->randomDigit,
                'version' => $this->faker->word,
                'author_name' => $this->faker->name,
                'author_email' => $this->faker->email,
                'support_name' => null,
                'support_email' => null,
                'sla' => null,
                'status' => $this->faker->randomDigit,
            ]);
        }
    }
}
