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

    public static function successResponse($data = [])
    {
        $response = ['success' => true];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return new JsonResponse($response, 200);
    }
}
