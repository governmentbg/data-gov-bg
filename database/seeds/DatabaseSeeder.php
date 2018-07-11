<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(PasswordResetSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(RoleRightSeeder::class);
        $this->call(NewsletterDigestLogSeeder::class);
        $this->call(LocaleSeeder::class);
        $this->call(OrganisationSeeder::class);
    }
}
