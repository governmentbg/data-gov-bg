<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataQuery extends Model
{
    use SoftDeletes;

    const FREQ_TYPE_HOUR = 1;
    const FREQ_TYPE_DAY = 2;
    const FREQ_TYPE_WEEK = 3;
    const FREQ_TYPE_MONTH = 4;
    
    protected $guarded = ['id'];
    protected $table = 'data_queries';

    public static function getFreqTypes()
    {
        return [
            self::FREQ_TYPE_HOUR    => __('custom.hour'),
            self::FREQ_TYPE_DAY     => __('custom.day'),
            self::FREQ_TYPE_WEEK    => __('custom.week'),
            self::FREQ_TYPE_MONTH   => __('custom.month'),
        ];
    }
    
    public function connection()
    {
        return $this->belongsTo('App\ConnectionSetting');
    }
}
