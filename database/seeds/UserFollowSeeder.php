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
        $user = User::where('username', 'system')->limit(self::USER_FOLLOW_RECORDS)->value('id');
        $organisations = Organisation::select('id')->where('type', '!=', Organisation::TYPE_GROUP)->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $groups = Organisation::select('id')->where('type', Organisation::TYPE_GROUP)->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $dataSets = DataSet::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $categories = Category::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();

        foreach (range(1, self::USER_FOLLOW_RECORDS) as $index) {
            $followedUser = $this->faker->randomElement($users)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];
            $group = $this->faker->randomElement($groups)['id'];
            $dataSet = $this->faker->randomElement($dataSets)['id'];
            $category = $this->faker->randomElement($categories)['id'];

            $followTypesArr = [
                ['org_id'         => $organisation],
                ['group_id'       => $group],
                ['data_set_id'    => $dataSet],
                ['category_id'    => $category],
                ['follow_user_id' => $followedUser]
            ];

            $followTypeArr = $this->faker->randomElement($followTypesArr);

            UserFollow::create(
                array_merge(
                    [
                        'user_id'        => $user,
                        'news'           => $this->faker->numberBetween(10,20),
                    ],
                    $followTypeArr
                )
            );
        }
    }
}
