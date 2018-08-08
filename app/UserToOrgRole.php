<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserToOrgRole extends Model
{
    protected $table = 'user_to_org_role';
    protected $guarded = [];
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation', 'org_id');
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
