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
use Elasticsearch\Common\Exceptions\RuntimeException;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;

include_once(base_path() . '/vendor/phayes/geophp/geoPHP.inc');

class ConversionController extends ApiController
{
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
                $xml = simplexml_load_string('<data>'. $post['data'] .'</data>');
                $json = json_encode($xml);
                $array = json_decode($json, true);

                return $this->successResponse($array);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid xml syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
            try {
                $data = $this->getXML($post['data']);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid json syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
                $temp = tmpfile();
                $path = stream_get_meta_data($temp)['uri'];
                fwrite($temp, $post['data']);
                $spreadsheet = IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = [];

                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    $cells = [];

                    foreach ($cellIterator as $cell) {
                        $cells[] = trim($cell->getValue());
                    }

                    $array[] = $cells;
                }

                fclose($temp);

                return $this->successResponse($array);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid csv syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
            try {
                $data = $this->getCSV($post['data']);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid json syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid kml syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
            try {
                $data = $this->getKML($post['data']);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid json kml syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid rdf syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
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
            try {
                $data = $this->getRDF($post['data']);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid json syntax!');
            }
        }

        return $this->errorResponse('Conversion failure', $validator->errors()->messages());
    }

    /**
     * Get ellastic search data
     *
     * @param string api_key - required
     * @param string es_id - required
     *
     * @return json with json data or error
     */
    public function toJSON(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['es_id' => 'required|int|exists:elastic_data_set,id']);

        if (!$validator->fails()) {
            try {
                $data = ElasticDataSet::getElasticData($post['es_id']);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get data failure', $validator->errors()->messages());
    }

    /**
     * Get ellastic search data
     *
     * @param string api_key - required
     * @param string es_id - required
     *
     * @return json with xml data or error
     */
    public function toXML(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['es_id' => 'required|int|exists:elastic_data_set,id']);

        if (!$validator->fails()) {
            try {
                $data = ElasticDataSet::getElasticData($post['es_id']);
                $data = $this->getXML($data);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get data failure', $validator->errors()->messages());
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
     * @param string es_id - required
     *
     * @return json with csv data or error
     */
    public function toCSV(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['es_id' => 'required|int|exists:elastic_data_set,id']);

        if (!$validator->fails()) {
            try {
                $data = ElasticDataSet::getElasticData($post['es_id']);
                $data = $this->getCSV($data);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get data failure', $validator->errors()->messages());
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
        $writer->setEnclosure('');
        $writer->save($path);

        $data = file_get_contents($path);

        fclose($temp);

        return $data;
    }

    /**
     * Get ellastic search data
     *
     * @param string api_key - required
     * @param string es_id - required
     *
     * @return json with kml data or error
     */
    public function toKML(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['es_id' => 'required|int|exists:elastic_data_set,id']);

        if (!$validator->fails()) {
            try {
                $data = ElasticDataSet::getElasticData($post['es_id']);
                $data = $this->getKML($data);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid json kml syntax!');
            }
        }

        return $this->errorResponse('Get data failure', $validator->errors()->messages());
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
     * Get ellastic search data
     *
     * @param string api_key - required
     * @param string es_id - required
     *
     * @return json with rdf data or error
     */
    public function toRDF(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['es_id' => 'required|int|exists:elastic_data_set,id']);

        if (!$validator->fails()) {
            try {
                $data = ElasticDataSet::getElasticData($post['es_id']);
                $data = $this->getRDF($data);

                return $this->successResponse($data);
            } catch (\ErrorException $ex) {
                $validator->errors()->add('data', 'Invalid json rdf syntax!');
            }
        }

        return $this->errorResponse('Get data failure', $validator->errors()->messages());
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
