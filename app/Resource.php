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
    const FORMAT_XML = 5;
    const FORMAT_WMS = 6;

    const HTTP_POST = 1;
    const HTTP_GET = 2;

    protected static $translatable = [
        'name'      => 'text',
        'descript'  => 'text',
    ];

    public static function getTypes()
    {
        return [
            self::TYPE_FILE         => uctrans('custom.file'),
            self::TYPE_HYPERLINK    => __('custom.hyperlink'),
            self::TYPE_API          => __('custom.api'),
        ];
    }

    public static function getFormatsCode($format)
    {
        switch (strtoupper($format)) {
            case 'CSV':
            case 'XLS':
            case 'XLSX':
                return self::FORMAT_CSV;
            case 'KML':
                return self::FORMAT_KML;
            case 'RDF':
                return self::FORMAT_RDF;
            case 'WMS':
                return self::FORMAT_WMS;
            case 'XML':
                return self::FORMAT_XML;
            default:
                return self::FORMAT_JSON;
        }
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
            'id'        => $this->id,
            'name'      => $this->concatTranslations('name'),
            'descript'  => $this->concatTranslations('descript'),
        ];
    }

    public function signal()
    {
       return $this->hasMany('App\Signal');
    }

    public function elasticDataSet()
    {
        return $this->hasOne('App\ElasticDataSet', 'id', 'es_id');
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
