<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\Api\ResourceController;

class CheckReportedResources
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
        $params['api_key'] = \Auth::user()->api_key;
        $params['user_id'] = \Auth::user()->id;

        $checkReq = Request::create('/api/hasReportedResource', 'POST', $params);
        $api = new ResourceController($checkReq);
        $reportedResFlag = $api->hasReportedResource($checkReq)->getData();

        if ($reportedResFlag->success) {
            View::share('hasReported', $reportedResFlag->flag);
        }

        return $next($request);
    }
}
