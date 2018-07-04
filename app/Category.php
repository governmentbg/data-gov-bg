<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];
    protected $table = 'categories';

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function dataSetSubCategory()
    {
        return $this->hasMany('App\DataSetSubCategories');
    }
}
