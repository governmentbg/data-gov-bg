<?php

use App\Role;
use App\Module;
use App\RoleRight;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertBaseRightsInRights extends Migration
{
    public function __construct()
    {
        $organisation = Role::where('default_org_admin', 1)->first();
        $group = Role::where('default_group_admin', 1)->first();
        $user = Role::where('default_user', 1)->first();

        $this->rights = [
            $organisation->id => [
                [
                    'module_name'       => Module::ORGANISATIONS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::USERS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::DATA_SETS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::RESOURCES,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::ROLES,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::ACTIONSHISTORY,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
            ],
            $group->id => [
                [
                    'module_name'       => Module::GROUPS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::DATA_SETS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::RESOURCES,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::USERS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::ROLES,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::TERMS_OF_USE,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::TERMS_OF_USE_REQUESTS,
                    'right'             => RoleRight::RIGHT_EDIT,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::CUSTOM_SETTINGS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::ACTIONSHISTORY,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
            ],
            $user->id => [
                [
                    'module_name'       => Module::ORGANISATIONS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 1,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::GROUPS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 1,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::DATA_SETS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 1,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::RESOURCES,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 1,
                    'api'               => 1,
                ],
                [
                    'module_name'       => Module::MAIN_CATEGORIES,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::TAGS,
                    'right'             => RoleRight::RIGHT_EDIT,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::TERMS_OF_USE,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::TERMS_OF_USE_REQUESTS,
                    'right'             => RoleRight::RIGHT_EDIT,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::USERS,
                    'right'             => RoleRight::RIGHT_ALL,
                    'limit_to_own_data' => 1,
                    'api'               => 0,
                ],
                [
                    'module_name'       => Module::ACTIONSHISTORY,
                    'right'             => RoleRight::RIGHT_VIEW,
                    'limit_to_own_data' => 0,
                    'api'               => 0,
                ],
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
        foreach ($this->rights as $roleId => $rightDataArray) {
            foreach ($rightDataArray as $rightData) {
                $rightData['module_name'] = Module::getModuleName($rightData['module_name']);
                $rightData = array_merge(['role_id' => $roleId], $rightData);

                if (!RoleRight::where($rightData)->count()) {
                    RoleRight::create($rightData);
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
        foreach ($this->rights as $roleId => $rightDataArray) {
            foreach ($rightDataArray as $rightData) {
                $rightData['module_name'] = Module::getModuleName($rightData['module_name']);
                $rightData = array_merge(['role_id' => $roleId], $rightData);

                if (RoleRight::where($rightData)->count()) {
                    RoleRight::delete($rightData);
                }
            }
        }
    }
}
