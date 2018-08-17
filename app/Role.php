<?php

namespace App;

use App\UserToOrgRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Role extends Model
{
    use RecordSignature;

    const ROLE_ADMIN = 1;
    const ROLE_MODERATOR = 2;
    const ROLE_MEMBER = 3;

    protected $guarded = ['id'];

    public static function getBaseRoles()
    {
        return [
            self::ROLE_ADMIN        => __('custom.admin'),
            self::ROLE_MODERATOR    => __('custom.moderator'),
            self::ROLE_MEMBER       => __('custom.member'),
        ];
    }

    public function rights()
    {
        return $this->hasMany('App\RoleRight');
    }

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
}
