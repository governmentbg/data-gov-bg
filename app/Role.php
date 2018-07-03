<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = ['id'];

    public function rights()
    {
        return $this->hasMany('App\RoleRight');
    }
}
