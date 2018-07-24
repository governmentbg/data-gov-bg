<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsletterDigestLog extends Model
{
    const TYPE_TEMP = 1;
    const TYPE_TEMP2 = 2;
    const TYPE_TEMP3 = 3;

    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'newsletter_digest_log';

    public static function getTypes()
    {
        return [
            self::TYPE_TEMP     => 'Temp',
            self::TYPE_TEMP2    => 'Temp2',
            self::TYPE_TEMP3    => 'Temp3',
        ];
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
