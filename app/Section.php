<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\RecordSignature;

class Section extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id', 'name'];

    protected static $translatable = [
        'name' => 'label',
    ];

    public function page()
    {
        return $this->hasMany('App\Page');
    }
}
