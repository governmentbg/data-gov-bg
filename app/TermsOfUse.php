<?php

namespace App;

use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class TermsOfUse extends Model
{
    use RecordSignature;
    use Translatable;

    protected $guarded = ['id'];
    protected $table = 'terms_of_use';

    protected static $translatable = [
        'name'      => 'label',
        'descript'  => 'text',
    ];

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }
}
