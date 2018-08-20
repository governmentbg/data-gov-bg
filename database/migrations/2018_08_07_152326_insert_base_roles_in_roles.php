<?php

use App\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertBaseRolesInRoles extends Migration
{
    public function __construct()
    {
        $this->roles = Role::getBaseRoles();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->roles as $role) {
            Role::create(array_merge([
                'name'      => $role,
                'active'    => true,
            ], $this->getDefaultRoleValues($role)));
        }
    }

    private function getDefaultRoleValues($role)
    {
        $array = [
            'default_user'          => false,
            'default_group_admin'   => false,
            'default_org_admin'     => false,
        ];

        if ($role == $this->roles[Role::ROLE_ADMIN]) {
            $array['default_group_admin'] = true;
            $array['default_org_admin'] = true;
        } else if ($role == $this->roles[Role::ROLE_MEMBER]) {
            $array['default_user'] = true;
        }

        return $array;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->roles as $role) {
            Role::where(['name' => $role])->get()->delete();
        }
    }
}
