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

    const SYSTEM_USERS = [
        'system',
        'migrate_data'
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

    public static function getUserRoles($id)
    {
        $user = User::find($id);

        if ($user) {
            $result = [];

            foreach($user->userToOrgRole as $role) {

                $roleRights = $role->role->rights;

                $rightResult = [];

                foreach($roleRights as $singleRoleRight) {
                    $rightResult[] = [
                        'module_name'          => $singleRoleRight->module_name,
                        'right'                => $singleRoleRight->right,
                        'limit_to_own_data'    => $singleRoleRight->limit_to_own_data,
                        'api'                  => $singleRoleRight->api
                    ];
                }

                $result[] = [
                    'org_id'    => $role->org_id,
                    'role_id'   => $role->role_id,
                    'rights'    => $rightResult

                ];

                unset($roleRights);
                unset($rightResult);
            }

            return $result;
        }

        return false;
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
