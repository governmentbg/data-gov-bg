<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Log\Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareActiveSections
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
        if (!config('app.IS_TOOL')) {
            $activeSections = app('App\Http\Controllers\Controller')->getActiveSections();
            View::share('activeSections', $activeSections);
        }

        return $next($request);
    }
}
