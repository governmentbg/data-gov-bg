<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $guarded = ['id'];

    public function userToOrgRole()
    {
        return $this->hasMany('App\UserToOrgRole');
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function orgCustomSetting()
    {
        return $this->hasOne('App\OrgCustomSetting');
    }

    public function dataSet()
    {
        return $this->hasMany('App\DataSet');
    }

    public function dataSetGroup()
    {
        return $this->hasMany('App\DataSetGroup');
    }

    public function dataRequest()
    {
        return $this->hasMany('App\DataRequest');
    }
}
