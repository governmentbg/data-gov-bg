<?php

use App\Tag;
use App\Role;
use App\User;
use App\Group;
use App\Dataset;
use App\Category;
use App\Resource;
use App\Organization;
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
        $modules = Role::MODULE_NAMES;

        $actionObjects = [];

        foreach ($modules as $module) {
            $table = class_exists('App\\'. $module)
                ? App::make('App\\'. $module)->getTable()
                : null;

            if ($table) {
                $actionObjects[$module] = DB::table($table)->select('id')
                    ->limit(self::ACTIONS_HISTORY_RECORDS)
                    ->get()
                    ->toArray();
            }
        }

        // Test creation
        foreach (range(1, self::ACTIONS_HISTORY_RECORDS) as $i) {
            $user = $this->faker->randomElement($users)['id'];
            $type = $this->faker->randomElement($types);
            $module = $this->faker->randomElement($modules);

            if (isset($actionObjects[$module])) {
                $actionObject = $this->faker->randomElement($actionObjects[$module])->id;
            } else {
                $actionObject = $this->faker->numberBetween(1,10);
            }

            ActionsHistory::create([
                'user_id'       => $user,
                'occurrence'    => $this->faker->dateTimeBetween('-1 years', 'now'),
                'module_name'   => $module,
                'action'        => $type,
                'action_object' => $actionObject,
                'action_msg'    => $this->faker->sentence(),
                'ip_address'    => $this->faker->ipv4(),
                'user_agent'    => $this->faker->sentence(),
            ]);
        }
    }
}
