<?php

namespace App;

use App\Contracts\TranslatableInterface;
use App\Http\Controllers\Traits\RecordSignature;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Page extends Model implements TranslatableInterface
{
    use RecordSignature;
    use Translatable;
    use Searchable;

    protected $guarded = ['id'];

    const DATE_TYPE_UPDATED = 'updated';
    const DATE_TYPE_CREATED = 'created';
    const DATE_TYPE_VALID = 'valid';

    protected static $translatable = [
        'title'             => 'label',
        'abstract'          => 'text',
        'body'              => 'text',
        'head_title'        => 'label',
        'meta_desctript'    => 'text',
        'meta_key_words'    => 'label',
    ];

    public function toSearchableArray()
    {
        return [
            'id'                => $this->id,
            'title'             => $this->concatTranslations('title'),
            'abstract'          => $this->concatTranslations('abstract'),
            'body'              => $this->concatTranslations('body'),
            'head_title'        => $this->concatTranslations('head_title'),
            'meta_desctript'    => $this->concatTranslations('meta_desctript'),
            'meta_key_words'    => $this->concatTranslations('meta_key_words'),
        ];
    }

    public function section()
    {
        return $this->belongsTo('App\Section');
    }

    public function searchableAs()
    {
        return 'pages';
    }
}
