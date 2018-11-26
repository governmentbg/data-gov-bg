<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConnectionSetting extends Model
{
    use SoftDeletes;

    const SOURCE_TYPE_DB = 1;
    const SOURCE_TYPE_FILE = 2;
    
    protected $guarded = ['id'];
    protected $table = 'connection_settings';

    public static function getSourceTypes()
    {
        return [
            self::SOURCE_TYPE_DB    => 'dbms',
            self::SOURCE_TYPE_FILE  => 'file',
        ];
    }
    
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
