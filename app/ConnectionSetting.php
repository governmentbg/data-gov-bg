<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConnectionSetting extends Model
{
    protected $guarded = ['id'];
    protected $table = 'connection_settings';
}
