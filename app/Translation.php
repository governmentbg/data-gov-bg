<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Translation extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    public function locale()
    {
        $this->belongsTo('App\Locale');
    }
}
