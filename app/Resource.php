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
    const FORMAT_TSV = 7;
    const FORMAT_XSD = 8;
    const FORMAT_ODS = 9;
    const FORMAT_SLK = 10;
    const FORMAT_RTF = 11;
    const FORMAT_ODT = 12;

    const HTTP_POST = 1;
    const HTTP_GET = 2;

    const REPORTED_FALSE = 0;
    const REPORTED_TRUE = 1;

    protected static $translatable = [
        'name'      => 'text',
        'descript'  => 'text',
    ];

    public static function getTransFields()
    {
        return self::$translatable;
    }

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
            case 'TSV':
                return self::FORMAT_TSV;
            case 'XSD':
                return self::FORMAT_XSD;
            case 'ODS':
                return self::FORMAT_ODS;
            case 'SLK':
                return self::FORMAT_SLK;
            case 'RTF':
                return self::FORMAT_RTF;
            case 'ODT':
                return self::FORMAT_ODT;
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
            self::FORMAT_TSV    => 'TSV',
            self::FORMAT_XSD    => 'XSD',
            self::FORMAT_ODS    => 'ODS',
            self::FORMAT_SLK    => 'SLK',
            self::FORMAT_RTF    => 'RTF',
            self::FORMAT_ODT    => 'ODT',
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
        return $this->hasMany('App\ElasticDataSet');
    }

    public function dataSet()
    {
        return $this->belongsTo('App\DataSet');
    }

    public function customFields()
    {
        return $this->hasMany('App\CustomSetting');
    }

    public function searchableAs()
    {
        return 'resources';
    }

    public static function getAllowedFormats()
    {
        return [
            'CSV', 'JSON', 'KML','RDF','WMS','XML','XLSX', 'XLS', 'TXT', 'TSV', 'XSD', 'ODS', 'SLK', 'RTF', 'ODT'
        ];
    }
}
