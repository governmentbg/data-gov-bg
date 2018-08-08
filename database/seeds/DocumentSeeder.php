<?php

use App\Locale;
use App\Document;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Http\Controllers\ApiController;

class DocumentSeeder extends Seeder
{
    const DOCUMENT_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $locales = Locale::where('active', 1)->limit(self::DOCUMENT_RECORDS)->get()->toArray();

        foreach (range(1, self::DOCUMENT_RECORDS) as $i) {
            $locale = $this->faker->randomElement($locales)['locale'];

            Document::create([
                'name'          => ApiController::trans($locale, $this->faker->randomDigit()),
                'descript'      => ApiController::trans($locale, $this->faker->randomDigit()),
                'file_name'     => $this->faker->word,
                'mime_type'     => $this->faker->word,
                'data'          => $this->faker->sentence(4)
            ])->searchable();
        }
    }
}
