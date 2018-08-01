<?php

use App\Section;
use App\Locale;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SectionTableSeeder extends Seeder
{
    const SECTION_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $locales = Locale::where('active', 1)->limit(self::SECTION_RECORDS)->get()->toArray();
        foreach (range(1, self::SECTION_RECORDS) as $i) {
            $locale = $this->faker->randomElement($locales)['locale'];
            Section::create([
                'name'      => [$locale=>$this->faker->word],
                'parent_id'    => 1,
                'active'    => $this->faker->boolean(),
                'ordering'    => $this->faker->boolean(),
                'read_only'    => $this->faker->boolean(),
                'forum_link'    => $this->faker->url(),
                'theme'    => $this->faker->randomDigit(),
            ]);
        }
    }
}
