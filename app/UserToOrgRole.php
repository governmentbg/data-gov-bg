<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserToOrgRole extends Model
{
    protected $table = 'user_to_org_role';
    protected $guarded = [];
    public $timestamps = false;

    const ROLE_ADMIN = 1;
    const ROLE_MODERATOR = 2;
    const ROLE_MEMBER = 3;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
