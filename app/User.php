<?php

namespace App;

use Laravel\Scout\Searchable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Traits\RecordSignature;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;
    use Searchable;
    use RecordSignature;

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

    public function toSearchableArray()
    {
        $array['id'] = $this->id;
        $array['firstname'] = $this->firstname;
        $array['lastname'] = $this->lastname;
        $array['username'] = $this->username;
        $array['email'] = $this->email;

        return $array;
    }
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
        return $this->hasMany('App\UserToOrgRole');
    }

    public function newsletterDigestLog()
    {
        return $this->hasMany('App\NewsletterDigestLog');
    }

    public function follow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function searchableAs()
    {
        return 'users';
    }
}
