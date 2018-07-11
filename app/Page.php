<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Page extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    public function section()
    {
        return $this->belongsTo('App\Section');
    }
}
