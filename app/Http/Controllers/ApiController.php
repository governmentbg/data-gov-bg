<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

    /**
     * Get records per page
     *
     * @return integer
     */
    public function getRecordsPerPage($number)
    {
        if (empty($number)) {
            return self::DEFAULT_RECORDS_PER_PAGE;
        }

        if ($number > self::MAX_RECORDS_PER_PAGE) {
            return self::DEFAULT_RECORDS_PER_PAGE;
        }

        return $number;
    }

    /**
     * Return error response
     *
     * @return json/xml - response data
     */
    public static function errorResponse($message = null, $errors = [], $code = 500, $type = self::ERROR_GENERAL)
    {
        $resposeData = [
            'success'   => false,
            'status'    => $code,
            'errors'    => $errors,
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

    /**
     * Return success response
     *
     * @return json/xml - response data
     */
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

                return response($xmlData->asXML(), 200)->header('Content-Type', 'text/xml');
            case 'json':
            default:
                return new JsonResponse($response, 200);
        }
    }

    /**
     * convert array to xml
     *
     * @param type $data
     * @param type $xmlData
     */
    public static function arrayToXml($data, &$xmlData) {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'. $key; //dealing with <0/>..<n/> issues
            }

            if(is_array($value)) {
                $subNode = $xmlData->addChild($key);

                self::arrayToXml($value, $subNode);
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     *
     * @param type $locale
     * @param type $value
     * @return type
     */
    public function trans($locale, $value, $groupId = 0)
    {
        $defaultLocale = \LaravelLocalization::getDefaultLocale();

        if ($groupId) {
            $haveDefautTrans = DB::table('translations')
                    ->where('locale', $defaultLocale)
                    ->where('group_id', $groupId)
                    ->count();
            if ($haveDefautTrans) {
                return [$locale => $value];
            }
        }

        if ($locale == $defaultLocale) {
            return $value;
        }

        return [$defaultLocale => $value, $locale => $value];
    }
}
