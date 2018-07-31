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
            self::DIGEST_FREQ_NONE          => 'Не желая',
            self::DIGEST_FREQ_ON_POST       => 'При публикуване',
            self::DIGEST_FREQ_ONCE_DAY      => 'Веднъж дневно',
            self::DIGEST_FREQ_ONCE_WEEK     => 'Веднъж седмично',
            self::DIGEST_FREQ_ONCE_MONTH    => 'Веднъж месечно',
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
