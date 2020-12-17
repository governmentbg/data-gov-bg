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

    protected static $translatable = ['title' => 'label'];

    protected static function boot() {
        parent::boot();

        static::deleting(function($section) {
            if (!empty($section->pages())) {
                if (!empty($section->pages())) {
                    foreach ($section->pages()->get() as $page) {
                        $page->section_id = null;
                        $page->save();
                    }
                }
            }

            if (!empty($section->subsections())) {
                foreach ($section->subsections()->get() as $subsec) {
                    if (!empty($subsec->pages())) {
                        foreach ($subsec->pages()->get() as $page) {
                            $page->section_id = null;
                            $page->save();
                        }
                    }
                }

                $section->subsections()->delete();
            }
        });
    }

    public function toSearchableArray()
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'title' => $this->concatTranslations('title'),
        ];
    }

    public function subsections()
    {
        return $this->hasMany('App\HelpSection', 'parent_id');
    }

    public function pages()
    {
        return $this->hasMany('App\HelpPage', 'section_id');
    }

    public function searchableAs()
    {
        return 'help_sections';
    }
}
