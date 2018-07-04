<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = ['id'];

    public function section()
    {
        return $this->belongsTo('App\Section');
    }
}
