<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class UserSetting extends Model
{
    use RecordSignature;

    protected $guarded = [];
    protected $primaryKey = 'user_id';

    const DIGEST_FREQ_NONE = 0;
    const DIGEST_FREQ_ON_POST = 1;
    const DIGEST_FREQ_ONCE_DAY = 2;
    const DIGEST_FREQ_ONCE_WEEK = 3;
    const DIGEST_FREQ_ONCE_MONTH = 4;

    public static function getDigestFreq()
    {
        return [
            self::DIGEST_FREQ_NONE          => __('custom.digest_freq_no'),
            self::DIGEST_FREQ_ON_POST       => __('custom.digest_freq_on_post'),
            self::DIGEST_FREQ_ONCE_DAY      => __('custom.digest_freq_once_day'),
            self::DIGEST_FREQ_ONCE_WEEK     => __('custom.digest_freq_once_week'),
            self::DIGEST_FREQ_ONCE_MONTH    => __('custom.digest_freq_once_month'),
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function locale()
    {
        return $this->belongsTo('App\Locale');
    }
}
