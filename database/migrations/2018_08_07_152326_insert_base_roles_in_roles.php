<?php

use App\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertBaseRolesInRoles extends Migration
{
    public function __construct()
    {
        $this->roles = [
            [
                'name'                  => 'Администратор на организация',
                'active'                => 1,
                'default_org_admin'     => 1,
                'for_org'               => 1,
            ],
            [
                'name'                  => 'Администратор на група',
                'active'                => 1,
                'default_group_admin'   => 1,
                'for_group'             => 1,
            ],
            [
                'name'                  => 'Обикновен потребител',
                'active'                => 1,
                'default_user'          => 1,
            ],
            [
                'name'                  => 'Редактор на организация',
                'active'                => 1,
            ],
            [
                'name'                  => 'Член на организация',
                'active'                => 1,
            ],
            [
                'name'                  => 'Редактор на група',
                'active'                => 1,
            ],
            [
                'name'                  => 'Член на група',
                'active'                => 1,
            ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            foreach ($this->roles as $role) {
                if (!Role::where(['name' => $role['name']])->count()) {
                    Role::create($role);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!env('IS_TOOL')) {
            foreach ($this->roles as $role) {
                if (Role::where(['name' => $role['name']])->count()) {
                    Role::where(['name' => $role['name']])->delete();
                }
            }
        }
    }
}
