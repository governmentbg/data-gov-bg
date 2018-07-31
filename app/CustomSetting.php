<?php

namespace App;

use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Http\Controllers\Traits\RecordSignature;

class CustomSetting extends Model implements TranslatableInterface
{
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];

    protected static $translatable = [
        'key'   => 'label',
        'value' => 'text',
    ];

    public function organisation()
    {
        return $this->belongsTo('App\Organisation');
    }
}
