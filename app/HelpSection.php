<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Http\Controllers\Traits\RecordSignature;

class HelpSection extends Model implements TranslatableInterface
{
    use Searchable;
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];
    protected $table = 'help_sections';

    protected static $translatable = ['name' => 'label'];

    public function toSearchableArray()
    {
        return [
            'id'   => $this->id,
            'name' => $this->concatTranslations('name'),
        ];
    }

    public function subsections()
    {
        return $this->hasMany('App\HelpSection', 'parent_id');
    }

    public function searchableAs()
    {
        return 'help_sections';
    }
}
