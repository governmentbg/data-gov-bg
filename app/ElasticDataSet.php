<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ElasticDataSet extends Model
{
    const ELASTIC_TYPE = 'default';

    protected $guarded = ['id'];
    protected $table = 'elastic_data_set';
    public $timestamps = false;

    public function resource()
    {
        $this->belongsTo('App\Resource');
    }
}
