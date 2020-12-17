<?php

namespace App;

use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Database\Eloquent\Model;

class DataRequest extends Model
{
    use RecordSignature;

    protected $guarded = ['id'];

    const NEW_DATA_REQUEST = 0;
    const PROCESSED_DATA_REQUEST = 1;

    public static function getDataRequestStatuses()
    {
        return [
            self::NEW_DATA_REQUEST        => 'new',
            self::PROCESSED_DATA_REQUEST  => 'processed',
        ];
    }

    public function organisation()
    {
        $this->belongsTo('App\Organisation');
    }
}
