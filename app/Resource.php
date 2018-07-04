<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $guarded = ['id'];

    public function signal()
    {
        $this->hasMany('App\Signal');
    }

    public function elasticDataSet()
    {
        $this->hasOne('App\ElasticDataSet');
    }
}
