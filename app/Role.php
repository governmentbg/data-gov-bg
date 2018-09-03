<?php

namespace App;

use App\UserToOrgRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Role extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    /**
     * RoleRights relation
     *
     */
    public function rights()
    {
        return $this->hasMany('App\RoleRight');
    }

    /**
     * UserToOrgRole relation
     *
     */
    public function userToOrg()
    {
        return $this->hasMany('App\UserToOrgRole');
    }

    //get the default role for organisation admin. Return null if the role is not found
    public static function getOrgAdminRole()
    {
        if ($orgAdminRole = Role::where('active', 1)->where('default_org_admin', 1)->first()) {
            return $orgAdminRole;
        }

        return null;
    }

    //get the default role for group admin. Return null if the role is not found
    public static function getGroupAdminRole()
    {
        if ($groupAdminRole = Role::where('active', 1)->where('default_group_admin', 1)->first()) {
            return $groupAdminRole;
        }

        return null;
    }

    public static function isAdmin($org = null, $group = null) {
        if (Auth::user()->is_admin) {
            return true;
        } elseif ($org) {
            $role = self::getOrgAdminRole();
            if (isset($role)) {
                return UserToOrgRole::where([
                    'user_id'   => Auth::user()->id,
                    'org_id'    => $org,
                    'role_id'   => $role->id
                ])->count();
            }
        } elseif ($group) {
            $role = self::getGroupAdminRole();
            if (isset($role)) {
                return UserToOrgRole::where([
                    'user_id'   => Auth::user()->id,
                    'org_id'    => $group,
                    'role_id'   => $role->id
                ])->count();
            }
        }

        return false;
    }

    /**
     * Check if user has enough rights for the given module
     *
     * @param string moduleName - comming from MODULE_NAMES (required)
     * @param integer rightType - comming from RIGHT_ constants (required)
     * @param integer data - used to check if record created_by for limit_to_own_data functionality (optional)
     * @param integer orgId - used to check if the right is for the specified organisation (optional)
     * @param boolean api - used to check if the right is for api or not (optional)
     *
     * @return boolean wheather user is authorized or not
     */
    public static function isAuthorized($moduleName, $rightType, $data = [], $orgId = false, $api = false) {
        if (Auth::check()) {
            if (Auth::user()->is_admin) {
                return true;
            }

            $params = [
                'userId'        => Auth::user()->id,
                'moduleName'    => $moduleName,
                'rightType'     => $rightType,
            ];

            if (!empty($orgId)) {
                $params['orgId'] = $orgId;
            }

            if (!empty($data['created_by']) && $data['created_by'] != $params['userId']) {
                $limitToOwnDataQuery = ' AND ('.
                    'rr.limit_to_own_data = 0 OR rr.limit_to_own_data IS NULL'.
                ')';
            }

            if (!empty($api)) {
                $apiQuery = ' AND rr.api = 1';
            }

            $result = DB::select(
                'SELECT rr.id FROM user_to_org_role AS utor'.
                ' LEFT JOIN roles AS r on utor.role_id = r.id'.
                ' LEFT JOIN role_rights AS rr on r.id = rr.role_id'.
                (empty($limitToOwnDataQuery) ? '' : $limitToOwnDataQuery) .
                (empty($apiQuery) ? '' : $apiQuery) .
                ' WHERE utor.user_id = :userId'.
                    ' AND utor.org_id '. (empty($orgId) ? 'IS NULL' : '= :orgId') .
                    ' AND rr.module_name = :moduleName'.
                    ' AND rr.right >= :rightType'.
                ' LIMIT 1', $params);
        }

        return !empty($result);
    }
}
