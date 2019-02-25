<?php

namespace App\Http\Controllers\Api;

use Uuid;
use App\DataSet;
use App\Category;
use App\Resource;
use SimpleXMLElement;
use App\ElasticDataSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Elasticsearch\Common\Exceptions\RuntimeException;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use App\Extensions\ExcelReader\Custom_Spreadsheet_Excel_Reader;

include_once(base_path() . '/vendor/phayes/geophp/geoPHP.inc');

class ConversionController extends ApiController
{
    public function __construct()
    {
        ini_set('max_execution_time', 300);
    }

    /**
     * Convert from xml data and return json
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with data or error
     */
    public function xml2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required|string']);

        if (!$validator->fails()) {
            try {
                $array = $this->fromXML($post['data'], true);

                return $this->successResponse($array);
            } catch (\Exception $ex) {
               Log::error($ex->getMessage());
               $validator->errors()->add('data', __('custom.invalid_xml'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from json data and return xml
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with xml data or error
     */
    public function json2xml(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            $post['data'] = is_string($post['data'])
                ? json_decode($post['data'], true)
                : $post['data'];

            try {
                $data = $this->getXML($post['data']);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_json'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from csv data and return json
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with data or error
     */
    public function csv2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required|string']);

        if (!$validator->fails()) {
            try {
                $file = new \SplFileObject('php://memory', 'w+');

                if (!mb_check_encoding($post['data'], 'UTF-8')) {
                    $post['data'] = mb_convert_encoding($post['data'], 'UTF-8', 'Windows-1251');
                }

                $file->fwrite($post['data']);
                $file->fseek(0);

                $data = [];

                while (!$file->eof()) {
                    $data[] = $file->fgetcsv();
                }

                if ($this->emptyRecursive($data)) {
                    return $this->errorResponse(__('custom.invalid_format_csv'));
                }

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_csv'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    private function emptyRecursive($value)
    {
        if (is_array($value)) {
            $empty = true;

            array_walk_recursive($value, function($item) use (&$empty) {
                $empty = $empty && empty($item);
            });
        } else {
            $empty = empty($value);
        }

        return $empty;
    }

    /**
     * Convert from json data and return csv
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with csv data or error
     */
    public function json2csv(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            $post['data'] = is_string($post['data'])
                ? json_decode($post['data'], true)
                : $post['data'];

            try {
                $data = $this->getCSV($post['data']);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_json'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from kml data and return json
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with data or error
     */
    public function kml2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required|string']);

        if (!$validator->fails()) {
            try {
                $geo = \geoPHP::load($post['data'], 'kml');

                if ($geo) {
                    return $this->successResponse(json_decode($geo->out('json'), true));
                }
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_kml'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from json data and return kml
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with kml data or error
     */
    public function json2kml(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            $post['data'] = is_string($post['data'])
                ? json_decode($post['data'], true)
                : $post['data'];

            try {
                $data = $this->getKML($post['data']);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_kml_json'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from rdf data and return json
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with data or error
     */
    public function rdf2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required|string']);

        if (!$validator->fails()) {
            try {
                $easyRdf = new \EasyRdf_Graph();

                $easyRdf->parse($post['data']);

                return $this->successResponse(json_decode($easyRdf->serialise('json')));
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_rdf'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from json data and return rdf
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with rdf data or error
     */
    public function json2rdf(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            $post['data'] = is_string($post['data'])
                ? json_decode($post['data'], true)
                : $post['data'];

            try {
                $data = $this->getRDF($post['data']);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_json'));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from pdf base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with pdf text or error
     */
    public function pdf2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $pages = 1;
                $path = storage_path('app/pdf-resource-'. uniqid());
                file_put_contents($path, base64_decode($post['data']));
                $paths = [$path];

                chmod($path, 0775);

                $im = new \Imagick();

                $im->setResolution(300, 300);
                $im->readimage($path);
                $im->setImageFormat('jpeg');
                $im->setImageDepth(8);
                $im->stripImage();
                $im->setBackgroundColor('white');

                $pages = $im->getNumberImages();

                if ($pages > 1) {
                    for ($i = 0; $i < $pages; $i++) {
                        $paths[] = $path .'-'. $i;
                    }
                }

                $im->writeImages($path, false);

                $result = '';

                foreach ($paths as $index => $singlePath) {
                    if ($pages == 1 || $index > 0) {
                        $result .= (new TesseractOCR($singlePath))->lang('bul', 'eng')->run() . PHP_EOL;
                    }
                }

                return $this->successResponse($result);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());

                $validator->errors()->add('data', __('custom.no_text_found'));
            } catch (\ErrorException $ex) {
                Log::error($ex->getMessage());

                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'pdf']));
            } finally {
                $im->clear();
                $im->destroy();

                foreach ($paths as $index => $singlePath) {
                    @unlink($singlePath);
                }
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from img base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with img text or error
     */
    public function img2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];

                file_put_contents($path, base64_decode($post['data']));

                $result = (new TesseractOCR($path))->lang('bul', 'eng')->run();

                fclose($temp);

                return $this->successResponse($result);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());

                $validator->errors()->add('data', __('custom.no_text_found'));
            } catch (\ErrorException $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'img']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from doc/docx base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with doc/docx text or error
     */
    public function doc2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $result = $this->fromWORD($post['data']);

                return $this->successResponse($result);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'doc/docx']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from xls/xlsx base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with xls/xlsx text or error
     */
    public function xls2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $data = $this->fromCells($post['data'], false);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'xls/xlsx']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from ods base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with ods text or error
     */
    public function ods2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $data = $this->fromCells($post['data'], false);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'ods']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from slk base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with slk text or error
     */
    public function slk2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $data = $this->fromCells($post['data'], false);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'slk']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from tsv data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with tsv text or error
     */
    public function tsv2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $data = base64_decode($post['data']);

                $data = explode("\r\n", $data);

                foreach ($data as $single) {
                    $data2d[] = explode("\t", $single);
                }

                return $this->successResponse($data2d);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'tsv']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from xsd data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with xsd text or error
     */
    public function xsd2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $data = base64_decode($post['data']);

                $data = explode("\r\n", $data);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'xsd']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from rtf base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with rtf text or error
     */
    public function rtf2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $result = $this->fromWORD($post['data']);

                return $this->successResponse($result);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'rtf']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Convert from odt base64 encoded data and return text
     *
     * @param string api_key - required
     * @param string data - required
     *
     * @return json with odt text or error
     */
    public function odt2json(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required']);

        if (!$validator->fails()) {
            try {
                $result = $this->fromWORD($post['data']);

                return $this->successResponse($result);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_file', ['type' => 'odt']));
            }
        }

        return $this->errorResponse(__('custom.converse_fail'), $validator->errors()->messages());
    }

    /**
     * Get elastic search data
     *
     * @param string api_key - required
     * @param string resource_id - required
     * @param string version - optional
     *
     * @return json with json data or error
     */
    public function toJSON(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_id'   => 'required|int|exists:resources,id',
            'version'       => 'sometimes|int|exists:elastic_data_set,version',
        ]);

        if (!$validator->fails()) {
            if (!isset($post['version'])) {
                $resource = Resource::find($post['resource_id']);
                $version = $resource->version;
            } else {
                $version = $post['version'];
            }

            try {
                $data = ElasticDataSet::getElasticData($post['resource_id'], $version);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.data_failure'), $validator->errors()->messages());
    }

    /**
     * Get elastic search data
     *
     * @param string api_key - required
     * @param string resource_id - required
     * @param string version - optional
     *
     * @return json with xml data or error
     */
    public function toXML(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_id'   => 'required|int|exists:resources,id',
            'version'       => 'sometimes|int|exists:elastic_data_set,version',
        ]);

        if (!$validator->fails()) {
            if (!isset($post['version'])) {
                $resource = Resource::find($post['resource_id']);
                $version = $resource->version;
            } else {
                $version = $post['version'];
            }

            try {
                $data = ElasticDataSet::getElasticData($post['resource_id'], $version);
                $data = $this->getXML($data);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.data_failure'), $validator->errors()->messages());
    }

    /**
     * Get xml from data
     *
     * @param array data - required
     *
     * @return xml data
     */
    private function getXML($data)
    {
        $xmlData = new SimpleXMLElement('<root/>');

        parent::arrayToXml($data, $xmlData);

        return $xmlData->asXML();
    }

    /**
     * Get ellastic search data
     *
     * @param string api_key - required
     * @param string resource_id - required
     * @param string version - optional
     *
     * @return json with csv data or error
     */
    public function toCSV(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_id'   => 'required|int|exists:resources,id',
            'version'       => 'sometimes|int|exists:elastic_data_set,version',
        ]);

        if (!$validator->fails()) {
            if (!isset($post['version'])) {
                $resource = Resource::find($post['resource_id']);
                $version = $resource->version;
            } else {
                $version = $post['version'];
            }

            try {
                $data = ElasticDataSet::getElasticData($post['resource_id'], $version);
                $data = $this->getCSV($data);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.data_failure'), $validator->errors()->messages());
    }

    /**
     * Get text from word document data
     *
     * @param word document data - required
     *
     * @return text data
     */
    private function fromWORD($data)
    {
        $result = '';
        $temp = tmpfile();
        $path = stream_get_meta_data($temp)['uri'];
        $tempOut = tmpfile();
        $pathOut = stream_get_meta_data($tempOut)['uri'];

        file_put_contents($path, base64_decode($data));

        if (mime_content_type($path) == 'application/msword') {
            shell_exec('/usr/bin/wvText '. escapeshellarg($path) .' '. escapeshellarg($pathOut));
            $result = file_get_contents($pathOut);

            if (!mb_detect_encoding($result, 'UTF-8', true)) {
                $result = utf8_encode($text);
            }
        } else {
            if (mime_content_type($path) == 'text/rtf') {
                $result = \PhpOffice\PhpWord\IOFactory::load($path, 'RTF');
            } else if (mime_content_type($path) == 'application/vnd.oasis.opendocument.text') {
                $result = \PhpOffice\PhpWord\IOFactory::load($path, 'ODText');
            } else {
                $result = \PhpOffice\PhpWord\IOFactory::load($path);
            }

            $result->save($pathOut, 'HTML');
            $result = $this->fromHTML(file_get_contents($pathOut));
        }

        fclose($temp);
        fclose($tempOut);

        return $result;
    }

    /**
     * Get text from csv/xls/xlsx document data
     *
     * @param csv/xls/xlsx document data - required
     * @param bool document data - optional
     *
     * @return text data
     */
    private function fromCells($data, $csv = true)
    {
        $temp = tmpfile();
        $path = stream_get_meta_data($temp)['uri'];
        fwrite($temp, $csv ? $data : base64_decode($data));

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = [];

        try {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $cells = [];

                foreach ($cellIterator as $cell) {
                    $value = trim($cell->getFormattedValue());
                    $cells[] = $value;
                }

                if (!empty($cells)) {
                    $rows[] = $cells;
                }
            }

            fclose($temp);

            $rowCount = count($rows);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }

        foreach ($rows[0] as $cellIndex => $cell) {
            if ($cell == '') {
                foreach ($rows as $row) {
                    if ($row[$cellIndex] != '') {
                        continue 2;
                    }
                }

                foreach ($rows as &$row) {
                    unset($row[$cellIndex]);
                }
            }
        }

        $spreadsheet->disconnectWorksheets();
        $spreadsheet->garbageCollect();
        unset($spreadsheet);

        return $rows;
    }

    /**
     * Get text from html data
     *
     * @param html data - required
     *
     * @return json data
     */
    private function fromHTML($data)
    {
        $html = new \Html2Text\Html2Text($data);

        return $html->getText();
    }

    /**
     * Get json from xml data
     *
     * @param xml data - required
     *
     * @return json data
     */
    private function fromXML($data, $parentTag = false)
    {
        if ($parentTag) {
            $data = '<data>'. htmlspecialchars($data, ENT_XML1, 'UTF-8') .'</data>';
        }

        $xml = simplexml_load_string($data);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return $array;
    }

    /**
     * Get csv from data
     *
     * @param array data - required
     *
     * @return csv data
     */
    private function getCSV($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);
        $writer = new Csv($spreadsheet);

        $temp = tmpfile();
        $path = stream_get_meta_data($temp)['uri'];
        $writer->setEnclosure('"');
        $writer->setUseBOM(true);
        $writer->save($path);

        $data = file_get_contents($path);

        fclose($temp);

        return $data;
    }

    /**
     * Get elastic search data
     *
     * @param string api_key - required
     * @param string resource_id - required
     * @param string version - optional
     *
     * @return json with kml data or error
     */
    public function toKML(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_id'   => 'required|int|exists:resources,id',
            'version'       => 'sometimes|int|exists:elastic_data_set,version',
        ]);

        if (!$validator->fails()) {
            if (!isset($post['version'])) {
                $resource = Resource::find($post['resource_id']);
                $version = $resource->version;
            } else {
                $version = $post['version'];
            }

            try {
                $data = ElasticDataSet::getElasticData($post['resource_id'], $version);
                $data = $this->getKML($data);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invalid_json_kml'));
            }
        }

        return $this->errorResponse(__('custom.data_failure'), $validator->errors()->messages());
    }

    /**
     * Get kml from data
     *
     * @param array data - required
     *
     * @return kml data
     */
    private function getKML($data)
    {
        $geo = \geoPHP::load(json_encode($data), 'json');

        return $geo ? $geo->out('kml') : null;
    }

    /**
     * Get elastic search data
     *
     * @param string api_key - required
     * @param string resource_id - required
     * @param string version - optional
     *
     * @return json with rdf data or error
     */
    public function toRDF(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_id'   => 'required|int|exists:resources,id',
            'version'       => 'sometimes|int|exists:elastic_data_set,version',
        ]);

        if (!$validator->fails()) {
            if (!isset($post['version'])) {
                $resource = Resource::find($post['resource_id']);
                $version = $resource->version;
            } else {
                $version = $post['version'];
            }

            try {
                $data = ElasticDataSet::getElasticData($post['resource_id'], $version);
                $data = $this->getRDF($data);

                return $this->successResponse($data);
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
                $validator->errors()->add('data', __('custom.invlid_json_rdf'));
            }
        }

        return $this->errorResponse(__('custom.data_failure'), $validator->errors()->messages());
    }

    /**
     * Get rdf from data
     *
     * @param array data - required
     *
     * @return rdf data
     */
    private function getRDF($data)
    {
        $easyRdf = new \EasyRdf_Graph();
        $easyRdf->parse(json_encode($data), 'json');

        return $easyRdf->serialise('rdfxml');
    }
}
