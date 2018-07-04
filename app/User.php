<?php

namespace App;

use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use RecordSignature;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function userSetting()
    {
        return $this->hasOne('App\UserSetting');
    }

    public function newsletterDigestLog()
    {
        return $this->hasMany('App\NewsletterDigestLog');
    }
}
