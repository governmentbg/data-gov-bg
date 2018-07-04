<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrgCustomSetting extends Model
{
    protected $guarded = ['id'];

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }
}
