<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class RoleRight extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];
    const RIGHT_VIEW = 1;
    const RIGHT_EDIT = 2;
    const RIGHT_ALL = 3;

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
