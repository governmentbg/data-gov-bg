<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use App\Http\Controllers\Traits\RecordSignature;

class DataSet extends Model implements TranslatableInterface
{
    protected $guarded = ['id'];

    use Translatable;
    use RecordSignature;

    protected static $translatable = [
        'name'          => 'label',
        'descript'      => 'text'    
    ];

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
}
