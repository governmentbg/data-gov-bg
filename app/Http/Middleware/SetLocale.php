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
        $locale = !empty($request->get('locale')) && !empty(DB::table('locale')->where('locale', $request->get('locale'))->value('locale'))
            ? $request->get('locale')
            : config('app.locale');

        $request->offsetUnset('locale');
        \LaravelLocalization::setLocale($locale);

        return $next($request);
    }
}
