<?php

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Locale;
use App\Page;
use App\Section;

class PageSeeder extends Seeder
{
    const PAGES_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $locales = Locale::where('active', 1)->limit(self::PAGES_RECORDS)->get()->toArray();
        $sections = Section::limit(self::PAGES_RECORDS)->get()->toArray();

        foreach (range(1, self::PAGES_RECORDS) as $i) {
            $locale = $this->faker->randomElement($locales)['locale'];
            \LaravelLocalization::setLocale($locale);
            $section = $this->faker->randomElement($sections)['id'];

            Page::create([
                'section_id'      => $section,
                'title'           => $this->faker->word(),
                'type'            => Page::TYPE_PAGE,
                'abstract'        => $this->faker->word(),
                'body'            => $this->faker->word(),
                'head_title'      => $this->faker->word(),
                'meta_descript'   => $this->faker->word(),
                'meta_key_words'  => $this->faker->word(),
                'forum_link'      => $this->faker->word,
                'active'          => $this->faker->boolean,
                'valid_from'      => $this->faker->date,
                'valid_to'        => $this->faker->date
            ]);
        }
    }
}
