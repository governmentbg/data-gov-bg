<?php

use App\User;
use App\Locale;
use App\NewsletterDigestLog;
use App\UserSetting;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UserSettingTableSeeder extends Seeder
{
    const USER_SETTING_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $users = User::select('id')->limit(self::USER_SETTING_RECORDS)->get()->toArray();
        $locales = Locale::where('active', 1)->limit(self::USER_SETTING_RECORDS)->get()->toArray();
        $newsLetters = NewsLetterDigestLog::select('id')->limit(self::USER_SETTING_RECORDS)->get()->toArray();

        foreach (range(1, self::USER_SETTING_RECORDS) as $index) {
            $user = $this->faker->unique()->randomElement($users)['id'];
            $locale = $this->faker->randomElement($locales)['locale'];
            $newsLetter = $this->faker->randomElement($newsLetters)['id'];

            UserSetting::create([
                'user_id'           => $user,
                'locale'            => $locale,
                'newsletter_digest' => $newsLetter
            ]);
        }
    }
}
