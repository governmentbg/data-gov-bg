<?php

namespace App;

use App\UserToOrgRole;
use App\Organisation;
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

    /**
     * get the default role for org admin. Return null if the role is not found
     *
     * @return role or null
     *
     */
    public static function getOrgAdminRole()
    {
        if ($orgAdminRole = Role::where('active', 1)->where('default_org_admin', 1)->first()) {
            return $orgAdminRole;
        }

        return null;
    }

    /**
     * get the default role for group admin. Return null if the role is not found
     *
     * @return role or null
     *
     */
    public static function getGroupAdminRole()
    {
        if ($groupAdminRole = Role::where('active', 1)->where('default_group_admin', 1)->first()) {
            return $groupAdminRole;
        }

        return null;
    }

    public static function isAdmin() {
        if (Auth::check()) {
            if (Auth::user()->is_admin) {
                return true;
            }
        }

        return false;
    }
}
