<?php

namespace App\Translator;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Translation extends Model {

    use RecordSignature;

    protected $table = 'translations';
    protected $fillable = ['group_id', 'locale', 'text', 'label'];
    public $timestamps = false;

    public function delete() {
        parent::delete();

        // Leave atleast one dummy translation for using this group_id
        // We do that so we will always have a foreign key in the main model
        // pointing to existing group_id in the translations table
        if ($this->locale !== 'xx') {
            if (!Translation::where('group_id', $this->group_id)->exists()) {
                Translation::create([
                    'group_id'  => $this->group_id,
                    'locale'    => 'xx',
                    'text'      => '',
                    'label'     => '',
                ]);
            }
        }
    }
}
