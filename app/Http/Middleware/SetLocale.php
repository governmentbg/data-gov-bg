<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App;

class SetLocale
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
        $locale = !empty($request->data['locale']) && !empty(DB::table('locale')->where('locale', $request->data['locale'])->value('locale'))
            ? $request->data['locale']
            : config('app.locale');

        $request->offsetUnset('data.locale');

        \LaravelLocalization::setLocale($locale);

        return $next($request);
    }
}
