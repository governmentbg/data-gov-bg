<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSetSubCategory extends Model
{
    protected $guarded = [];
    protected $table = 'data_set_sub_categories';
    public $timestamps = false;

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }
}
