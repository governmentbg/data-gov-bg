<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use App\Http\Controllers\Traits\RecordSignature;

class Category extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];
    protected $table = 'categories';

    protected static $translatable = [
        'name'          => 'label'    
    ];

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function dataSetSubCategory()
    {
        return $this->hasMany('App\DataSetSubCategories');
    }
}
