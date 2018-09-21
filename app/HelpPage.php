<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Http\Controllers\Traits\RecordSignature;

class HelpPage extends Model implements TranslatableInterface
{
    use Searchable;
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];
    protected $table = 'help_pages';

    protected static $translatable = [
        'title' => 'label',
        'body'  => 'text',
    ];

    public function toSearchableArray()
    {
        return [
            'id'        => $this->id,
            'keywords'  => $this->keywords,
            'title'     => $this->concatTranslations('title'),
            'body'      => $this->concatTranslations('body'),
        ];
    }

    public function section()
    {
        return $this->belongsTo('App\HelpSection', 'section_id', 'id');
    }

    public function searchableAs()
    {
        return 'help_pages';
    }
}
