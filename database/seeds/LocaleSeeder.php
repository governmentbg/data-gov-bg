<?php

use App\Locale;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Locale::all()->count()) {
            $languages = [
                'bg'    => true,
                'en'    => true,
                'ru'    => false,
            ];

            foreach ($languages as $language => $active) {
                Locale::create([
                    'locale'    => $language,
                    'active'    => $active,
                ]);
            }
        }
    }
}
