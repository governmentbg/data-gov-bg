<?php

namespace App;

use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    public function resource()
    {
        $this->belongsTo('App\Resource');
    }
}
