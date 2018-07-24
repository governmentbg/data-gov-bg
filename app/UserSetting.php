<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class UserSetting extends Model
{
    use RecordSignature;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function locale()
    {
        return $this->belongsTo('App\Locale');
    }
}
