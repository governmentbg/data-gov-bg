<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use App\Http\Controllers\Traits\RecordSignature;

class Resource extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];

    protected static $translatable = [
        'name'          => 'label',
        'descript'       => 'text',
    ];
    
    public function signal()
    {
        $this->hasMany('App\Signal');
    }

    public function elasticDataSet()
    {
        $this->hasOne('App\ElasticDataSet');
    }
}
