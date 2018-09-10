<?php

namespace App;

use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    const STATUS_NEW = 1;
    const STATUS_PROCESSED = 2;

    public static function getStatuses()
    {
        return [
            self::STATUS_NEW        => 'new',
            self::STATUS_PROCESSED  => 'processed',
        ];
    }

    public function resource()
    {
        $this->belongsTo('App\Resource');
    }
}
