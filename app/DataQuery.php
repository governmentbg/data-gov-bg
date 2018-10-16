<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataQuery extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'data_queries';

    public function connection()
    {
        return $this->belongsTo('App\ConnectionSetting');
    }
}
