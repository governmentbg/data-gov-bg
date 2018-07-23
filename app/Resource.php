<?php

namespace App;

use Laravel\Scout\Searchable;
use App\Translator\Translatable;
use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\Traits\RecordSignature;

class Resource extends Model implements TranslatableInterface
{
    use Searchable;
    use SoftDeletes;
    use Translatable;
    use RecordSignature;

    protected $guarded = ['id'];

    const TYPE_FILE = 1;
    const TYPE_HYPERLINK = 2;
    const TYPE_API = 3;

    const FORMAT_CSV = 1;
    const FORMAT_JSON = 2;
    const FORMAT_KML = 3;
    const FORMAT_RDF = 4;
    const FORMAT_WMS = 5;
    const FORMAT_XML = 6;

    const HTTP_POST = 1;
    const HTTP_GET = 2;

    protected static $translatable = [
        'name'      => 'label',
        'descript'  => 'text',
    ];

    public static function getTypes()
    {
        return [
            self::TYPE_FILE         => 'File',
            self::TYPE_HYPERLINK    => 'Hyperlink',
            self::TYPE_API          => 'Api',
        ];
    }

    public static function getFormats()
    {
        return [
            self::FORMAT_CSV    => 'CSV',
            self::FORMAT_JSON   => 'JSON',
            self::FORMAT_KML    => 'KML',
            self::FORMAT_RDF    => 'RDF',
            self::FORMAT_WMS    => 'WMS',
            self::FORMAT_XML    => 'XML',
        ];
    }

    public static function getRequestTypes()
    {
        return [
            self::HTTP_POST => 'POST',
            self::HTTP_GET  => 'GET',
        ];
    }

    public function toSearchableArray()
    {
        return [
            'name'      => $this->concatTranslations('name'),
            'descript'  => $this->concatTranslations('descript'),
            'id'        => $this->id,
        ];
    }

    public function signal()
    {
       return $this->hasMany('App\Signal');
    }

    public function elasticDataSet()
    {
        return $this->hasOne('App\ElasticDataSet', 'id');
    }

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }

    public function searchableAs()
    {
        return 'resources';
    }
}
