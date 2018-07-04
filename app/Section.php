<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $guarded = ['id'];

    public function page()
    {
        return $this->hasMany('App\Page');
    }
}
