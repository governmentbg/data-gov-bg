<?php

namespace App;

use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;

class DataRequest extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    const NEW_DATA_REQUEST = 0;

    public function organisation()
    {
        $this->belongsTo('App\Organisation');
    }
}
