<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataQuery extends Model
{
    protected $guarded = ['id'];
    protected $table = 'data_queries';
}
