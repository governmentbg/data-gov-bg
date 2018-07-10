<?php

namespace App;

use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Traits\RecordSignature;

class Organisation extends Model implements TranslatableInterface
{
    use Translatable;
    use SoftDeletes;
    use RecordSignature;

    const TYPE_CIVILIAN = 1;
    const TYPE_COUNTRY = 2;
    const TYPE_GROUP = 3;

    protected $guarded = ['id'];

    protected static $translatable = [
        'name'          => 'label',
        'descript'      => 'text',
        'activity_info' => 'text',
        'contacts'      => 'label',
    ];

    public static function getTypes()
    {
        return [
            self::TYPE_CIVILIAN => 'Temp',
            self::TYPE_COUNTRY  => 'Temp2',
            self::TYPE_GROUP    => 'Temp3',
        ];
    }

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
