<?php

use App\User;
use Faker\Factory as Faker;
use App\NewsletterDigestLog;
use Illuminate\Database\Seeder;

class NewsletterDigestLogSeeder extends Seeder
{
    const NEWSLETTER_DIGEST_LOG_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $types = array_keys(NewsletterDigestLog::getTypes());
        $users = User::orderBy('created_at', 'desc')->limit(self::NEWSLETTER_DIGEST_LOG_RECORDS)->get()->toArray();

        foreach ($users as $user) {
            NewsletterDigestLog::create([
                'user_id'   => $user['id'],
                'type'      => $this->faker->randomElement($types),
                'sent'      => $this->faker->dateTime(),
            ]);
        }
    }
}