<?php

use App\User;
use App\UserFollow;
use App\Organisation;
use App\DataSet;
use App\Category;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UserFollowSeeder extends Seeder
{
    const USER_FOLLOW_RECORDS = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();
        $users = User::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $dataSets = DataSet::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $categories = Category::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();

        foreach (range(1, self::USER_FOLLOW_RECORDS) as $index) {
            $user = $this->faker->randomElement($users)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];
            $dataSet = $this->faker->randomElement($dataSets)['id'];
            $category = $this->faker->randomElement($categories)['id'];

            UserFollow::create([
                'user_id'     => $user,
                'org_id'      => $organisation,
                'data_set_id' => $dataSet,
                'category_id' => $category,
                'news'        => $this->faker->numberBetween(10,20)
            ]);
        }
    }
}
