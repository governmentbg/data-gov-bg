<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use App\User;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = $request->has('apiKey') ? $request->apiKey : $request->json('apiKey');

        if (!\App\User::where('api_key', $key)->get()->count()) {
            $response = new JsonResponse([
                'status' => 'error',
                'error' => [
                    'errorCode'    => 403,
                    'errorMessage' => 'Access denied',
                ],
            ], 403);

            return $response;
        }

        return $next($request);
    }
}
