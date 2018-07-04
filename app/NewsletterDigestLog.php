<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsletterDigestLog extends Model
{
    protected $table = 'newsletter_digest_log';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
