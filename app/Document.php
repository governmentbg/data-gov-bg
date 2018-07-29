<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use App\Http\Controllers\Traits\RecordSignature;
use Laravel\Scout\Searchable;

class Document extends Model implements TranslatableInterface
{
    use RecordSignature;
    use Translatable;
    use Searchable;

    protected $guarded = ['id'];

    protected static $translatable = [
        'name'      => 'label',
        'descript'  => 'text'
    ];

    public function toSearchableArray()
    {
        return [
            'name'      => $this->concatTranslations('name'),
            'descript'  => $this->concatTranslations('descript'),
            'id'        => $this->id,
        ];
    }

    const DATE_TYPE_UPDATED = 'updated';
    const DATE_TYPE_CREATED = 'created';
}
