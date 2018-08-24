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

    /**
     * Base roles included in the initial migration
     *
     */
    const ROLE_ADMIN = 1;
    const ROLE_MODERATOR = 2;
    const ROLE_MEMBER = 3;

    /**
     * Right types
     *
     */
    const RIGHT_VIEW = 1;
    const RIGHT_EDIT = 2;
    const RIGHT_ALL = 3;

    /**
     * Module names used to specify the different areas for authorization
     * Always add new module at the end of the array in case the are used by the array key
     *
     */
    const MODULE_NAMES = [
        'Category',
        'Tag',
        'Organisation',
        'Group',
        'User',
        'Dataset',
        'Resource',
    ];

    protected $guarded = ['id'];

    /**
     * Get base role types
     *
     */
    public static function getBaseRoles()
    {
        return [
            self::ROLE_ADMIN        => 'Admin',
            self::ROLE_MODERATOR    => 'Moderator',
            self::ROLE_MEMBER       => 'Member',
        ];
    }

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
     * Get all module names
     *
     */
    public static function getModuleNames()
    {
        return self::MODULE_NAMES;
    }

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

    public static function isAdmin($org = null) {
        if (Auth::user()->is_admin) {
            return true;
        } elseif ($org) {
            return UserToOrgRole::where([
                'user_id'   => Auth::user()->id,
                'org_id'    => $org,
                'role_id'   => self::ROLE_ADMIN,
            ])->count();
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
