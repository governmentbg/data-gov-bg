<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Log\Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ChatterPostChange
{
    const HTTP_NOT_FOUND = 404;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->status() != self::HTTP_NOT_FOUND) {
            $prevSegments = explode('/', parse_url(URL::previous())['path']);

            if (isset($prevSegments[1]) && $prevSegments[1] != config('chatter.routes.home')) {
                return back();
            }
        } else {
            return back()->with('alert-danger', __('custom.action_error'));
        }

        return $response;
    }
}
