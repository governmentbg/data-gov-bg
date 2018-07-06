<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;

class Organisation extends Model implements TranslatableInterface
{
    use Translatable;

    protected $guarded = ['id'];

    protected static $translatable = [
        'name'          => 'label',
        'descript'      => 'text',
        'activity_info' => 'text',
        'contacts'      => 'label',
    ];

    public function userToOrgRole()
    {
        return $this->hasMany('App\UserToOrgRole');
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function orgCustomSetting()
    {
        return $this->hasOne('App\OrgCustomSetting');
    }

    public function dataSet()
    {
        return $this->hasMany('App\DataSet');
    }

    public function dataSetGroup()
    {
        return $this->hasMany('App\DataSetGroup');
    }

    public function dataRequest()
    {
        return $this->hasMany('App\DataRequest');
    }
}
