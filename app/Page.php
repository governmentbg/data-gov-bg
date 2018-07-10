<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use App\Http\Controllers\Traits\RecordSignature;

class Page extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;
    
    protected $guarded = ['id'];

    protected static $translatable = [
        'title'          => 'label',
        'abstract'       => 'text',
        'body'           => 'text',
        'head_title'     => 'label',
        'meta_desctript' => 'text',
        'meta_key_words' => 'label'
    ];

    public function section()
    {
        return $this->belongsTo('App\Section');
    }
}
