<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataSetTags extends Model
{
    protected $guarded = [];
    protected $table = 'data_set_tags';
    public $timestamps = false;

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }

    public function tag()
    {
        return $this->belongsTo('App\Tag');
    }
}
