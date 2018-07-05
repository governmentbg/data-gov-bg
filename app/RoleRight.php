<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleRight extends Model
{
    const RIGHT_VIEW = 1;
    const RIGHT_EDIT = 2;
    const RIGHT_ALL = 3;

    protected $guarded = ['id'];

    public static function getRights()
    {
        return [
            self::RIGHT_VIEW    => 'View',
            self::RIGHT_EDIT    => 'Edit',
            self::RIGHT_ALL     => 'All',
        ];
    }

    public static function getRightsDescription()
    {
        return [
            self::RIGHT_VIEW    => 'View',
            self::RIGHT_EDIT    => 'View and edit',
            self::RIGHT_ALL     => 'View, edit and delete',
        ];
    }


    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
