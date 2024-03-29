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
  const TYPE_AUTO = 4;

  const FORMAT_CSV = 1;
  const FORMAT_JSON = 2;
  const FORMAT_KML = 3;
  const FORMAT_RDF = 4;
  const FORMAT_XML = 5;
  const FORMAT_TSV = 6;
  const FORMAT_XSD = 7;
  const FORMAT_ODS = 8;
  const FORMAT_SLK = 9;
  const FORMAT_RTF = 10;
  const FORMAT_ODT = 11;
  const FORMAT_ZIP = 12;

  const HTTP_POST = 1;
  const HTTP_GET = 2;

  const REPORTED_FALSE = 0;
  const REPORTED_TRUE = 1;

  // Conversion not available: format => ['not available formats']
  const FORMAT_LIMITS = [
    'KML' => ['CSV', 'RDF', 'ZIP'],
    'CSV' => ['KML', 'RDF', 'ZIP'],
    'JSON' => ['KML', 'RDF', 'ZIP'],
    'XML' => ['KML', 'RDF', 'CSV', 'ZIP'],
    'RDF' => ['KML', 'CSV', 'ZIP'],
    'XSD' => ['KML', 'RDF', 'ZIP'],
    'ZIP' => []
  ];

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
      self::TYPE_AUTO         => __('custom.auto_upload'),
    ];
  }

  public static function getFormatsCode($format)
  {
    switch (strtoupper($format)) {
      case 'TSV':
      case 'ODS':
      case 'SLK':
      case 'CSV':
      case 'XLS':
      case 'XLSX':
        return self::FORMAT_CSV;
      case 'KML':
        return self::FORMAT_KML;
      case 'RDF':
        return self::FORMAT_RDF;
      case 'XML':
        return self::FORMAT_XML;
      case 'XSD':
      case 'RTF':
      case 'ODT':
        return self::FORMAT_XSD;
      case 'ZIP':
        return self::FORMAT_ZIP;
      default:
        return self::FORMAT_JSON;
    }
  }

  public static function getFormats($forDownload = false)
  {
    $formats = [
      self::FORMAT_CSV    => 'CSV',
      self::FORMAT_JSON   => 'JSON',
      self::FORMAT_KML    => 'KML',
      self::FORMAT_RDF    => 'RDF',
      self::FORMAT_XML    => 'XML',
      self::FORMAT_ZIP    => 'ZIP',
    ];

    if (!$forDownload) {
      $formats = $formats + [
          self::FORMAT_TSV    => 'TSV',
          self::FORMAT_XSD    => 'XSD',
          self::FORMAT_ODS    => 'ODS',
          self::FORMAT_SLK    => 'SLK',
          self::FORMAT_RTF    => 'RTF',
          self::FORMAT_ODT    => 'ODT',
        ];
    }

    return $formats;
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
      'CSV', 'JSON', 'KML', 'RDF', 'XML', 'XLSX', 'XLS', 'TXT', 'TSV', 'XSD', 'ODS', 'SLK', 'RTF', 'ODT', 'PDF', 'DOC', 'DOCX'
    ];
  }

  public static function getResourceVersionFormat ($resource) {

    $resourceVersionFormat = null;

    if ($resource->file_format) {
      $versionQuery = ElasticDataSet::where('resource_id', $resource->id);

      if (is_null($resource->version)) {
        $versionQuery->orderBy('id', 'desc');
      } else {
        $versionQuery->where('version', $resource->version);
      }

      $versionResult = $versionQuery->first();

      if ($versionResult) {
        $resourceVersionFormat = $versionResult->format;
      }
    }

    $resourceVersionFormat = is_null($resourceVersionFormat) ? Resource::getFormatsCode($resource->file_format) : $resourceVersionFormat;

    return $resourceVersionFormat;
  }

  public static function getResourceZipFile($uri) {

    $zipDir = "../storage/app/files/$uri";
    $zipDirFull = "{$_SERVER['DOCUMENT_ROOT']}/storage/app/files/$uri";
    $zipName = "";

    if(file_exists($zipDir) && is_dir($zipDir)) {

      $files = scandir($zipDir);

      foreach ($files as $fileName) {
        if(strstr($fileName, 'zip')) {
          $zipFile = $zipDir.DIRECTORY_SEPARATOR.$fileName;
          $zipFileFull = $zipDirFull.DIRECTORY_SEPARATOR.$fileName;
          if(
            file_exists($zipFile)
            &&
            strtoupper(pathinfo($zipFileFull, PATHINFO_EXTENSION)) == self::getFormats()[self::FORMAT_ZIP]
          ) {
            $zipName = $fileName;
          }
        }
      }
    }

    return $zipName;
  }
}