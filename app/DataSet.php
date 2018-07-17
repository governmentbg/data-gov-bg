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


    public function toSearchableArray()
    {
        $array['name'] = $this->concatTranslations('name');
        $array['descript'] = $this->concatTranslations('descript');
        $array['sla'] = $this->concatTranslations('sla');
        $array['id'] = $this->id;

        return $array;
    }

    //check translation connection
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
