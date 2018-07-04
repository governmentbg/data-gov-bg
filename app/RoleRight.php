<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class RoleRight extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    public function role()
    {
        return $this->belongsTo('App\Role');
    }
}
