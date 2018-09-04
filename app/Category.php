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
            'name' => $this->concatTranslations('name'),
        ];
    }

}
