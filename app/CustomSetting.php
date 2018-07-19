<?php

namespace App;

use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;

class CustomSetting extends Model implements TranslatableInterface
{
    use Translatable;

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
