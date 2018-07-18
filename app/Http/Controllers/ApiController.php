<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    const ERROR_GENERAL = 'General';
    const DEFAULT_RECORDS_PER_PAGE = 50;
    const MAX_RECORDS_PER_PAGE = 100;

    protected static $format;

    /**
     * Create a new API controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        self::$format = $request->format;

        $validator = \Validator::make($request->all(), [
            'format'    => Rule::in(['json', 'xml']),
        ]);

        if (is_null(self::$format) || $validator->fails()) {
            self::$format = 'json';
        }
    }

    public function getRecordsPerPage(&$number)
    {
        if (empty($number)) {
            return self::DEFAULT_RECORDS_PER_PAGE;
        }

        if ($number > self::MAX_RECORDS_PER_PAGE) {
            return self::DEFAULT_RECORDS_PER_PAGE;
        }

        return $number;
    }

    public static function errorResponse($message = null, $code = 500, $type = self::ERROR_GENERAL)
    {
        $resposeData = [
            'success'   => false,
            'status'    => $code,
            'error'     => [
                'type'      => $type,
                'message'   => $message,
            ]
        ];

        switch (self::$format) {
            case 'xml':
                // creating object of SimpleXMLElement
                $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');

                // function call to convert array to xml
                self::arrayToXml($resposeData, $xmlData);

                return response($xmlData->asXML(), 500)
                    ->header('Content-Type', 'text/xml');
            case 'json':
            default :
                return new JsonResponse($resposeData, $code);
        }
    }

    public static function successResponse($data = [], $dataMerge = false)
    {
        $response = ['success' => true];

        if (!empty($data)) {
            if ($dataMerge) {
                $response = array_merge($response, $data);
            } else {
                $response['data'] = $data;
            }
        }

        switch (self::$format) {
            case 'xml':
                // creating object of SimpleXMLElement
                $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');

                // function call to convert array to xml
                self::arrayToXml($response, $xmlData);

                return response($xmlData->asXML(), 200)
                    ->header('Content-Type', 'text/xml');
            case 'json':
            default :
                return new JsonResponse($response, 200);
        }
    }

    /**
     * convert array to xml
     *
     * @param type $data
     * @param type $xml_data
     */
    public static function arrayToXml($data, &$xml_data) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }

            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                self::arrayToXml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }
}
