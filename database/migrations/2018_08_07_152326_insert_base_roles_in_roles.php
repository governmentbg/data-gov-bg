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
                 'name'              => 'Organisation Admin',
                 'active'            => 1,
                 'default_org_admin' => 1,
                 'for_org'           => 1
            ],
            [
                 'name'                => 'Group Admin',
                 'active'              => 1,
                 'default_group_admin' => 1,
                 'for_group'           => 1
            ],
            [
                 'name'              => 'User',
                 'active'            => 1,
                 'default_user'      => 1
            ]
         ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->roles as $role) {
            Role::create($role);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->roles as $role) {
            Role::where(['name' => $role['name']])->get()->delete();
        }
    }
}
