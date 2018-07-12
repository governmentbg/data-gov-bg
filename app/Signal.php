<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Signal extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    const TYPE_NEW = 1;
   
    public function resource()
    {
        $this->belongsTo('App\Resource');
    }
}
