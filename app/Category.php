<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Http\Controllers\Traits\RecordSignature;

class Category extends Model implements TranslatableInterface
{
    use Searchable;
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];
    protected $table = 'categories';

    protected static $translatable = ['name' => 'label'];

    const ORDERING_ASC = 1;
    const ORDERING_DESC = 2;

    const ACTIVE_FALSE = 0;

    const IMG_EXT_SVG = 'svg';
    const IMG_MIMES_SVG = ['text/plain', 'text/html', 'image/svg+xml', 'application/svg+xml'];

    public static function getTransFields()
    {
        return self::$translatable;
    }

    public static function getOrdering() {
        return [
            self::ORDERING_ASC  => __('custom.order_asc'),
            self::ORDERING_DESC => __('custom.order_desc'),
        ];
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function dataSet()
    {
        return $this->hasMany('App\DataSet');
    }

    public function dataSetSubCategory()
    {
        return $this->belongsToMany('App\DataSet', 'data_set_sub_categories', 'sub_cat_id', 'data_set_id');
    }

    public function tags()
    {
        return $this->hasMany('App\Category', 'parent_id');
    }

    public function toSearchableArray()
    {
        return [
            'id'   => $this->id,
            'name' => $this->concatTranslations('name'),
        ];
    }

}
