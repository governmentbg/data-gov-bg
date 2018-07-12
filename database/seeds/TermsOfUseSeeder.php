<?php

use App\TermsOfUse;
use Faker\Factory as Faker;
use App\Locale;
use Illuminate\Database\Seeder;

class TermsOfUseSeeder extends Seeder
{
    const TERMS_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $locales = Locale::where('active', 1)->limit(self::TERMS_RECORDS)->get()->toArray();
        foreach (range(1, self::TERMS_RECORDS) as $i) {
            $locale = $this->faker->randomElement($locales)['locale'];
            TermsOfUse::create([
                'name'          => [$locale => $this->faker->randomDigit()],
                'descript'      => [$locale => $this->faker->randomDigit()],
                'active'        => $this->faker->boolean(),
                'is_default'    => $this->faker->boolean(),
                'ordering'      => $this->faker->randomDigit(),
            ]);
        }
    }
}
