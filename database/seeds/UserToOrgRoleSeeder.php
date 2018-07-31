<?php

use App\User;
use App\Role;
use App\Organisation;
use App\UserToOrgRole;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UserToOrgRoleSeeder extends Seeder
{
    const USER_TO_ORG_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $users = User::select('id')->limit(self::USER_TO_ORG_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::USER_TO_ORG_RECORDS)->get()->toArray();
        $roles = Role::select('id')->limit(self::USER_TO_ORG_RECORDS)->get()->toArray();

        foreach (range(1, self::USER_TO_ORG_RECORDS) as $index) {
            $user = $this->faker->unique()->randomElement($users)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];
            $role = $this->faker->randomElement($roles)['id'];

            UserToOrgRole::create([
                'user_id' => $user,
                'org_id'  => $organisation,
                'role_id' => $role
            ]);
        }
    }
}
