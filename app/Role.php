<?php

namespace App;

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
            self::ROLE_ADMIN        => 'Admin',
            self::ROLE_MODERATOR    => 'Moderator',
            self::ROLE_MEMBER       => 'Member',
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
}
