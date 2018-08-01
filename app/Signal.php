<?php

namespace App;

use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;

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
