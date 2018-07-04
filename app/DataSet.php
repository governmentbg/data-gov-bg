<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSet extends Model
{
    protected $guarded = ['id'];

    //check translation connection
    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function dataSetGroup()
    {
        return $this->hasMany('App\DataSetGroup');
    }

    public function dataSetSubCategory()
    {
        return $this->hasMany('App\DataSetSubCategory');
    }

    public function resource()
    {
        return $this->hasMany('App\Resource');
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }
}
