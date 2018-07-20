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
        'title' => 'label',
        'abstract' => 'text',
        'body' => 'text',
        'head_title' => 'label',
        'meta_desctript' => 'text',
        'meta_key_words' => 'label',
    ];

    public function toSearchableArray()
    {
        $array['title'] = $this->concatTranslations('title');
        $array['abstract'] = $this->concatTranslations('abstract');
        $array['body'] = $this->concatTranslations('body');
        $array['head_title'] = $this->concatTranslations('head_title');
        $array['meta_desctript'] = $this->concatTranslations('meta_desctript');
        $array['meta_key_words'] = $this->concatTranslations('meta_key_words');
        $array['id'] = $this->id;

        return $array;
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
