<?php

use App\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertBaseRolesInRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (Role::getBaseRoles() as $role) {
            Role::create([
                'name'      => $role,
                'active'    => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (Role::getBaseRoles() as $role) {
            Role::where(['name' => $role])->get()->delete();
        }
    }
}
