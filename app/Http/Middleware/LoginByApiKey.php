<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Illuminate\Log\Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class LoginByApiKey
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
        if (!config('app.IS_TOOL') && !config('app.IS_TEST_TOOL')) {
            if (!Auth::check() && $request->offsetGet('api_key')) {
                $user = User::select('id')->where([
                    'api_key'   => $request->offsetGet('api_key'),
                    'active'    => 1,
                ])->first();

                if (!empty($user)) {
                    Auth::loginUsingId($user->id);
                }
            }
        }

        return $next($request);
    }
}
