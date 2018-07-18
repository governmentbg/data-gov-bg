<?php

namespace App\Http\Middleware;

use Closure;
use App\Locale;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class SetLocale
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
        if (isset($request->locale)) {
            $locale = $request->locale;
        } else if (isset($request->data['locale'])) {
            $locale = $request->data['locale'];
        } else if (isset($request->criteria['locale'])) {
            $locale = $request->criteria['locale'];
        }

        if (isset($locale)) {
            if (Locale::where('locale', $locale)->count()) {
                \LaravelLocalization::setLocale($locale);
            } else {
                return ApiController::errorResponse('Language `'. $locale .'` does not exist in database');
            }
        }

        return $next($request);
    }
}
