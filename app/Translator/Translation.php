<?php

namespace App\Translator;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Translation extends Model
{
    use RecordSignature;

    protected $table = 'translations';
    protected $fillable = ['group_id', 'locale', 'text', 'label'];
    public $timestamps = false;

    public function delete()
    {
        parent::delete();
    }
}
