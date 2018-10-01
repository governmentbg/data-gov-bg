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
        $this->call(TermsOfUseSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(DataRequestSeeder::class);
        $this->call(DataSetSeeder::class);
        $this->call(TagsSeeder::class);
        $this->call(DataSetTagsSeeder::class);
        $this->call(DataSetGroupSeeder::class);
        $this->call(ResourceSeeder::class);
        $this->call(SignalSeeder::class);
        $this->call(ElasticDataSetSeeder::class);
        $this->call(TermsOfUseRequestSeeder::class);
        $this->call(UserFollowSeeder::class);
        $this->call(UserSettingSeeder::class);
        $this->call(UserToOrgRoleSeeder::class);
        $this->call(CustomSettingSeeder::class);
        $this->call(ActionsHistorySeeder::class);
    }
}
