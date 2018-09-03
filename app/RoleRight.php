<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ActionsHistory;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Traits\RecordSignature;

class RoleRight extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    /**
     * Right types
     *
     */
    const RIGHT_VIEW = 1;
    const RIGHT_EDIT = 2;
    const RIGHT_ALL = 3;

        /**
     * Get translated right types and keys
     *
     */
    public static function getRights()
    {
        return [
            self::RIGHT_VIEW    => __('custom.view_right'),
            self::RIGHT_EDIT    => __('custom.edit_right'),
            self::RIGHT_ALL     => __('custom.all_right'),
        ];
    }

    /**
     * Get translated right types descriptions and keys
     *
     */
    public static function getRightsDescription()
    {
        return [
            self::RIGHT_VIEW    => __('custom.view_right_desc'),
            self::RIGHT_EDIT    => __('custom.edit_right_desc'),
            self::RIGHT_ALL     => __('custom.all_right_desc'),
        ];
    }

    /**
     * checkUserRight
     *
     * @param string $module
     * @param integer $rightType
     * @param array $checkData
     * @param array $objData
     * @return void
     */
    public static function checkUserRight($module, $rightType, $checkData = array(), $objData = array())
    {
        //return true if user is portal admin
        if (Role::isAdmin()) {
            return true;
        }

        $checkData['user_id'] = \Auth::user()->id;
        $checkData['check_api'] = false;

        $rolesArray = session()->get('roles');

        //Used when the request comes from an api and a session is not created.
        if (empty($rolesArray)) {
            $rq = Request::create('/api/getUserRoles', 'POST', ['id' => $checkData['user_id']]);
            $api = new UserController($rq);
            $result = $api->getUserRoles($rq)->getData();
            $checkData['check_api'] = true;

            if ($result->success) {
                $rolesArray = json_decode(json_encode($result->data->roles), true);
            }
        }

        //if error reading from database
        if (empty($rolesArray)) {
            return false;
        }

        foreach ($rolesArray as $singleRole) {
            if (!empty($singleRole['rights'])) {
                foreach ($singleRole['rights'] as $singleRight) {
                    if ($singleRight['module_name'] == $module) {
                        if ($singleRight['right'] >= $rightType) {
                            if (!empty($checkData)) {   //check additional right settings
                                if (isset($checkData['check_api']) && !empty($checkData['check_api'])) {
                                    if (!$singleRight['api']) { //action not allowed through api
                                        continue;
                                    }
                                }

                                $check = false;

                                if (isset($checkData['user_id']) && !empty($checkData['user_id'])
                                    && isset($objData['created_by']) && !empty($objData['created_by'])) {
                                    if ($singleRight['limit_to_own_data'] == 1) {
                                        if ($checkData['user_id'] == $objData['created_by']) {
                                            return true;
                                        }
                                    }
                                }

                                if (isset($checkData['org_id']) && !empty($checkData['org_id'])
                                    && isset($objData['org_id']) && !empty($objData['org_id'])) {
                                    $check = true;
                                    if ($singleRole['org_id'] == $objData['org_id']) {
                                        return true;
                                    }
                                }

                                if (isset($checkData['group_id']) && !empty($checkData['group_id'])
                                    && isset($objData['group_ids']) && !empty($objData['group_ids'])) {
                                    $check = true;
                                    if (in_array($singleRole['org_id'], $objData['group_ids'])) {
                                        return true;
                                    }
                                }

                                if (!$check) {
                                    return true;
                                }
                            } else {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
