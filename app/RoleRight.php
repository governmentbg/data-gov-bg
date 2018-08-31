<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ActionsHistory;
use App\Role;
use App\Http\Controllers\Traits\RecordSignature;

class RoleRight extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    /**
     * Maps action types to right types.
     * New actions must be added to the mapping
     *
     */
    public static function mapActions($actionType)
    {
        switch ($actionType) {
            case ActionsHistory::TYPE_SEE :
            case ActionsHistory::TYPE_FOLLOW :
            case ActionsHistory::TYPE_UNFOLLOW :
                $rightType = Role::RIGHT_VIEW;
                break;
            case ActionsHistory::TYPE_ADD :
            case ActionsHistory::TYPE_MOD :
            case ActionsHistory::TYPE_ADD_MEMBER :
            case ActionsHistory::TYPE_EDIT_MEMBER :
            case ActionsHistory::TYPE_ADD_GROUP :
            case ActionsHistory::TYPE_EDIT_GROUP :
                $rightType = Role::RIGHT_EDIT;
                break;
            case ActionsHistory::TYPE_DEL :
            case ActionsHistory::TYPE_DEL_MEMBER :
            case ActionsHistory::TYPE_DEL_GROUP :
                $rightType = Role::RIGHT_ALL;
                break;
            default :
                $rightType = '';
        }

        return $rightType;
    }

    // protected $checkData = array (
    //     'check_api' => true/false,
    //     'user_id'    => int,
    //     'org_id'     => int
    // );

    // protected $objData = array (
    //     'created_by' => int,
    //     'org_id'     => int,
    //     'group_ids'  => array (
    //        'group_id',
    //        'group_id'
    //     )
    // );

    public static function checkUserRight($module, $action, $checkData = array(), $objData = array())
    {
        $rolesArray = session()->get('roles');

        $checkData['user_id'] = \Auth::user()->id;

        if (empty($rolesArray)) {
            return false;
        }

        $rightType = self::mapActions($action);

        if (empty($rightType)) {
            return false;
        }

        foreach ($rolesArray as $singleRoleArray) {
            $arrayList = json_decode(json_encode($singleRoleArray), true);
        }

        foreach ($arrayList as $singleRole) {
            if (!empty($singleRole['rights'])) {
                foreach ($singleRole['rights'] as $singleRight) {
                    if ($singleRight['module_name'] == $module) {
                        if ($singleRight['right_id'] >= $rightType) {
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
                                        dd('6th');
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
