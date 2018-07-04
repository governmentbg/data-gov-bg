<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserToOrgRole extends Model
{
    protected $table = 'user_to_org_role';
    protected $guarded = [];

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
