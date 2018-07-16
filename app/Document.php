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

    protected static $translatable = [
        'name' => 'label',
        'descript' => 'text'
    ];

    public function toSearchableArray()
    {
        $array['name'] = $this->concatTranslations('name');
        $array['descript'] = $this->concatTranslations('descript'); 
        $array['id'] = $this->id;
        return $array;
    }

    const DATE_TYPE_UPDATED = 'updated';
    const DATE_TYPE_CREATED = 'created';

    protected $guarded = ['id']; 
}
