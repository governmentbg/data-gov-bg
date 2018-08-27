<?php

namespace App;

use Laravel\Scout\Searchable;
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
    use Searchable;

    const INIT_FILTER = 10;

    const TYPE_CIVILIAN = 1;
    const TYPE_COUNTRY = 2;
    const TYPE_GROUP = 3;

    const ACTIVE_FALSE = 0;
    const ACTIVE_TRUE = 1;
    const APPROVED_FALSE = 0;
    const APPROVED_TRUE = 1;

    protected $guarded = ['id'];

    protected static $translatable = [
        'name'          => 'label',
        'descript'      => 'text',
        'activity_info' => 'text',
        'contacts'      => 'text',
    ];

    public static function getTypes()
    {
        return [
            self::TYPE_CIVILIAN => 'Temp',
            self::TYPE_COUNTRY  => 'Temp2',
            self::TYPE_GROUP    => 'Temp3',
        ];
    }

    public static function getPublicTypes()
    {
        return [
            self::TYPE_CIVILIAN => 'custom.civilian',
            self::TYPE_COUNTRY  => 'custom.municipal',
        ];
    }

    public function toSearchableArray()
    {
        return [
            'id'            => $this->id,
            'name'          => $this->concatTranslations('name'),
            'descript'      => $this->concatTranslations('descript'),
            'activity_info' => $this->concatTranslations('activity_info'),
            'contacts'      => $this->concatTranslations('contacts'),
        ];
    }

    public function userToOrgRole()
    {
        return $this->hasMany('App\UserToOrgRole', 'org_id');
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow', 'org_id');
    }

    public function customSetting()
    {
        return $this->hasMany('App\CustomSetting', 'org_id');
    }

    public function dataSet()
    {
        return $this->hasMany('App\DataSet', 'org_id');
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
