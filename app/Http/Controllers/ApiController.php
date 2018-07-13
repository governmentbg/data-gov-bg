<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    const ERROR_GENERAL = 'General';

    public static function errorResponse($message = null, $code = 500, $type = self::ERROR_GENERAL)
    {
        return new JsonResponse([
            'success'   => false,
            'status'    => $code,
            'error'     => [
                'type'      => $type,
                'message'   => $message,
            ]
        ], $code);
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

        return new JsonResponse($response, 200);
    }

    /**
     * Get image data from URL
     *
     * @param string $url
     * @return array $result - array with image name, mime type, binary data
     */
    public function getImgDataFromUrl($url)
    {
        $result = [
            'name' => null,
            'mime' => null,
            'data' => null,
        ];

        if ($url) {
            if ($img = base64_encode(file_get_contents($url))) {
                $urlPieces = explode('/', $url);
                $result['name'] = !empty($urlPieces) ? $urlPieces[count($urlPieces) - 1] : null;
                $imgInfo = getimagesizefromstring(base64_decode($img));
                $result['mime'] = !empty($imgInfo['mime']) ? $imgInfo['mime'] : null;
                $result['data'] = base64_decode($img);
            }
        }

        return $result;
    }
}
