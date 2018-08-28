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

    public static function checkUserRight($module, $action, $userId, $objectId = null)
    {
        //за да се покаже даден бутон към списък се проверява дали има вю право - за организация -> Органисатионс view
            //ако кликне линка апито трябва да провери дали е цъкнат own data - ако цъкнат връща само моите организации.
            //objectId - не е задължително другите са задължителни

            //$objectId, $action, $userId, $module
        $rolesArray = session()->get('roles');

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
                            // if ($singleRight['limit_to_own_data'] == true) {

                            // }
                            return true;
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
