<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Traits\RecordSignature;

class DataSet extends Model implements TranslatableInterface
{
    use Searchable;
    use SoftDeletes;
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];

    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;

    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PRIVATE = 2;

    protected static $translatable = [
        'name'      => 'text',
        'descript'  => 'text',
        'sla'       => 'text',
    ];

    public function getDescriptionAttribute()
    {
        return $this->descript;
    }

    public static function getStatus()
    {
        return [
            self::STATUS_DRAFT      => uctrans('custom.draft'),
            self::STATUS_PUBLISHED  => uctrans('custom.published'),
        ];
    }

    public static function getVisibility()
    {
        return [
            self::VISIBILITY_PUBLIC     => __('custom.public'),
            self::VISIBILITY_PRIVATE    => __('custom.private'),
        ];
    }

    public function toSearchableArray()
    {
        return [
            'id'            => $this->id,
            'name'          => $this->concatTranslations('name'),
            'descript'      => $this->concatTranslations('descript'),
            'sla'           => $this->concatTranslations('sla'),
            'source'        => $this->source,
            'author_name'   => $this->author_name,
            'author_email'  => $this->author_email,
            'support_name'  => $this->support_name,
            'support_email' => $this->support_email,
            'custom_fields' => $this->concatCustomSettings(),
        ];
    }

    private function concatCustomSettings()
    {
        $settings = '';

        foreach ($this->customSetting()->get() as $setting) {
            $settings .= $setting->concatTranslations('key') .' '. $setting->concatTranslations('value');
        }

        return $settings;
    }

    public function organisation()
    {
        return $this->belongsTo('App\Organisation', 'org_id');
    }

    public function dataSetGroup()
    {
        return $this->hasMany('App\DataSetGroup');
    }

    public function dataSetTags()
    {
        return $this->hasMany('App\DataSetTags');
    }

    public function tags()
    {
        return $this->belongsToMany('App\Tags', 'data_set_tags', 'data_set_id', 'tag_id');
    }

    public function resource()
    {
        return $this->hasMany('App\Resource', 'data_set_id');
    }

    public function userFollow()
    {
        return $this->hasMany('App\UserFollow');
    }

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public function searchableAs()
    {
        return 'data_sets';
    }

    public function customSetting()
    {
        return $this->hasMany('App\CustomSetting', 'data_set_id');
    }
}
