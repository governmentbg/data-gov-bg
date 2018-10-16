<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConnectionSetting extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'connection_settings';

    protected static function boot() {
        parent::boot();

        static::deleting(function($connection) {
            $connection->dataQueries()->delete();
        });
    }

    public function dataQueries()
    {
        return $this->hasMany('App\DataQuery', 'connection_id');
    }
}
