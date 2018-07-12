<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use App\Http\Controllers\Traits\RecordSignature;

class TermsOfUse extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;
    
    protected $guarded = ['id'];
    protected $table = 'terms_of_use';
 
    protected static $translatable = [
        'name'          => 'label',
        'descript'       => 'text',
    ];

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }
}
