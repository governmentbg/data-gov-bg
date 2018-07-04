<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataRequest extends Model
{
    protected $guarded = ['id'];

    public function organisation()
    {
        $this->belongsTo('App\Organisation');
    }
}
