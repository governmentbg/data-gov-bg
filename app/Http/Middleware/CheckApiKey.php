<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Log\Logger;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\User;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = User::select('id')->where([
            'api_key'   => $request->offsetGet('api_key'),
            'active'    => 1,
        ])->first();

        if (
            !empty($user)
            && \Auth::loginUsingId($user->id)
        ) {
            $request->offsetUnset('api_key');

            return $next($request);
        }

        return ApiController::errorResponse('Access denied', [], 403);
    }
}
