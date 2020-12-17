<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSetGroup extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'data_set_id';

    public $timestamps = false;

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }
}
