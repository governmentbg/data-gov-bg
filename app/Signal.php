<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    protected $guarded = ['id'];

    public function resource()
    {
        $this->belongsTo('App\Resource');
    }
}
