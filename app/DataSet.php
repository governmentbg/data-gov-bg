<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Traits\RecordSignature;

class DataSet extends Model implements TranslatableInterface
{
    use Searchable;
    use SoftDeletes;
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];

    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PRIVATE = 2;

    protected static $translatable = [
        'name'      => 'label',
        'descript'  => 'text',
        'sla'       => 'text',
    ];

    public static function getStatus()
    {
        return [
            self::STATUS_DRAFT      => 'Draft',
            self::STATUS_PUBLISHED  => 'Published',
        ];
    }

    public static function getVisibility()
    {
        return [
            self::VISIBILITY_PUBLIC     => 'Public',
            self::VISIBILITY_PRIVATE    => 'Private',
        ];
    }

    public function toSearchableArray()
    {
        return [
            'id'        => $this->id,
            'name'      => $this->concatTranslations('name'),
            'descript'  => $this->concatTranslations('descript'),
            'sla'       => $this->concatTranslations('sla'),
        ];
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }

    public function dataSetGroup()
    {
        return $this->hasMany('App\DataSetGroup');
    }

    public function dataSetSubCategory()
    {
        return $this->hasMany('App\DataSetSubCategory');
    }

    public function resource()
    {
        return $this->hasMany('App\Resource');
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public function searchableAs()
    {
        return 'data_sets';
    }
}
