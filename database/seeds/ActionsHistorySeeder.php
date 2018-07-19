<?php

use App\User;
use App\ActionsHistory;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ActionsHistorySeeder extends Seeder
{
    const ACTIONS_HISTORY_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $users = User::select('id')->limit(self::ACTIONS_HISTORY_RECORDS)->get()->toArray();
        $types = array_keys(ActionsHistory::getTypes());
        $modules = ActionsHistory::MODULE_NAMES;

        // Test creation
        foreach (range(1, self::ACTIONS_HISTORY_RECORDS) as $i) {
            $user = $this->faker->randomElement($users)['id'];
            $type = $this->faker->randomElement($types);
            $module = $this->faker->randomElement($modules);

            ActionsHistory::create([
                'user_id'       => $user,
                'occurrence'     => $this->faker->dateTime(),
                'module_name'   => $module,
                'action'        => $type,
                'action_object' => $this->faker->sentence(),
                'action_msg'    => $this->faker->sentence(),
                'ip_address'    => $this->faker->ipv4(),
                'user_agent'    => $this->faker->sentence(),
            ]);
        }
    }
}
