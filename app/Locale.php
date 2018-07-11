<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    protected $table = 'locale';
    protected $guarded = ['id'];

    public function userSettings()
    {
        return $this->hasMany('App\UserSetting');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation');
    }
}
