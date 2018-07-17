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
        if ($request->has('data.locale')) {
            $locale = !empty(DB::table('locale')->where('locale',$request['data']['locale'])->value('locale'))
                ? $request['data']['locale']
                : null;

            $request->offsetUnset('data.locale');
        }

        if ($request->has('criteria.locale')) {
            $locale = !empty(DB::table('locale')->where('locale', $request->get('criteria.locale'))->value('locale'))
                ? $request->get('criteria.locale')
                : null;

            $request->offsetUnset('criteria.locale');
        }

        if ($request->has('locale')) {
            $locale = !empty(DB::table('locale')->where('locale', $request->get('locale'))->value('locale'))
                ? $request->get('locale')
                : null;

            $request->offsetUnset('locale');
        }

        \LaravelLocalization::setLocale(empty($locale) ? config('app.locale') : $locale);

        return $next($request);
    }
}
