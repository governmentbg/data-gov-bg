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

    const DATE_TYPE_UPDATED = 'updated';
    const DATE_TYPE_CREATED = 'created';

    protected $guarded = ['id'];

    protected static $translatable = [
        'name'      => 'label',
        'descript'  => 'text'
    ];

    public static function getTransFields()
    {
        return self::$translatable;
    }

    public function toSearchableArray()
    {
        return [
            'name'      => $this->concatTranslations('name'),
            'descript'  => $this->concatTranslations('descript'),
            'id'        => $this->id,
        ];
    }
}
