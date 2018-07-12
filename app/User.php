<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;
    use RecordSignature;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'firstname',
        'lastname',
        'add_info',
        'is_admin',
        'active',
        'approved',
        'api_key',
        'hash_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the system user
     *
     * @return User
     */
    public static function getSystem()
    {
        return User::where('username', 'system')->first();
    }

    public function userSetting()
    {
        return $this->hasOne('App\UserSetting');
    }

    public function userToOrgRole()
    {
        return $this->hasOne('App\UserToOrgRole');
    }

    public function newsletterDigestLog()
    {
        return $this->hasMany('App\NewsletterDigestLog');
    }

    public function follow()
    {
        return $this->hasMany('App\UserFollow');
    }
}
