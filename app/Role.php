<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Role extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    public function rights()
    {
        return $this->hasMany('App\RoleRight');
    }

    public function userToOrg()
    {
        return $this->hasMany('App\UserToOrgRole');
    }
}
