<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    const ERROR_GENERAL = 'custom.general';
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
                'type'      => __($type),
                'message'   => $message,
            ]
        ];

        switch (self::$format) {
            case 'xml':
                // creating object of SimpleXMLElement
                $xmlData = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');

                // function call to convert array to xml
                self::arrayToXml($resposeData, $xmlData);

                return response($xmlData->asXML(), 500)->header('Content-Type', 'text/xml');
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
     * Convert array to xml
     *
     * @param array $data
     * @param string $xmlData
     */
    public static function arrayToXml($data, &$xmlData) {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item'. $key; //dealing with <0/>..<n/> issues
            }

            if (is_array($value)) {
                $subNode = $xmlData->addChild($key);

                self::arrayToXml($value, $subNode);
            } else {
                $xmlData->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * Get correct value to set in translatable fields
     *
     * @param string|null $locale
     * @param string|array $data
     */
    public static function trans(&$locale, $data, $isUpdate = false) {
        $defaultLocale = \LaravelLocalization::getDefaultLocale();

        if (isset($locale)) {
            $array = [$locale => $data];

            if ($isUpdate || $locale == $defaultLocale) {
                return $array;
            }

            return array_merge([$defaultLocale => $data], $array);
        }

        if (is_array($data)) {
            $locales = array_keys($data);

            if ($isUpdate || in_array($defaultLocale, $locales)) {
                return $data;
            }

            return array_merge([$defaultLocale => $data[$locales[0]]], $data);
        }

        return [[]];
    }

     /* Check if image data is less than the max image size
     *
     * @param string $imageData
     *
     * @return bool $flag
     */
    protected function checkImageSize($imageData)
    {
        return (strlen(bin2hex($imageData)) / 2) <= env('IMAGE_MAX_SIZE', 16777215);
    }

    /**
     * Get image size error message
     *
     * @return string $text
     */
    protected function getImageSizeError()
    {
        return __('custom.image_size_too_big'). env('IMAGE_MAX_SIZE', 16777215) . __('custom.bytes');
    }
}
