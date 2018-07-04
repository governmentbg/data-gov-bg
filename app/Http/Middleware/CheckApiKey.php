<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
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
    public function handle(Request $request, Closure $next)
    {
        $user = User::select('id')->where('api_key', $request->get('api_key'))->first();

        if (
            !empty($user)
            && \Auth::loginUsingId($user->id, true)
        ) {
            $request->offsetUnset('api_key');

            return $next($request);
        }

        return ApiController::errorResponse('Access denied', 403);
    }
}
