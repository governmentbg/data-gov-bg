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
        $user = User::select('id')->where('api_key', $request->get('api_key'))->first();

        if (
            !empty($user)
            && \Auth::loginUsingId($user->id, true)
        ) {
            return $next($request);
        }

        return new JsonResponse([
            'success'   => false,
            'error'     => [
                'type'    => 'General',
                'message' => 'Access denied',
            ],
        ], 403);
    }
}
