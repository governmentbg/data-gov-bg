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

    const ACTIVE_FALSE = 0;
    const READ_ONLY_FALSE = 0;
    const LOCATION_MAIN_MENU = 1;
    const LOCATION_FOOTER = 2;

    public static function getSectionLocations()
    {
        return [self::LOCATION_MAIN_MENU => __('custom.main_menu_section'), self::LOCATION_FOOTER => __('custom.footer_section')];
    }

    public function page()
    {
        return $this->hasMany('App\Page');
    }
}
