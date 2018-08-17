<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class RoleRight extends Model
{
    use RecordSignature;

    const RIGHT_VIEW = 1;
    const RIGHT_EDIT = 2;
    const RIGHT_ALL = 3;

    protected $guarded = ['id'];

    public static function getRights()
    {
        return [
            self::RIGHT_VIEW    => __('custom.view_right'),
            self::RIGHT_EDIT    => __('custom.edit_right'),
            self::RIGHT_ALL     => __('custom.all_right'),
        ];
    }

    public static function getRightsDescription()
    {
        return [
            self::RIGHT_VIEW    => __('custom.view_right_desc'),
            self::RIGHT_EDIT    => __('custom.edit_right_desc'),
            self::RIGHT_ALL     => __('custom.all_right_desc'),
        ];
    }


    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
