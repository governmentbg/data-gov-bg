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
     * @param \Illuminate\Http\Request $request
     * @param \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $action = $request->route()->getAction();

        if ($request->route()->getPrefix() == 'api' && isset($action['controller'])) {
            $controller = explode('@', class_basename($action['controller']))[0];

            if ($controller != 'LocaleController') {
                if (isset($request->locale)) {
                    $locale = $request->locale;
                } else if (isset($request->data['locale'])) {
                    $locale = $request->data['locale'];
                } else if (isset($request->criteria['locale'])) {
                    $locale = $request->criteria['locale'];
                } else if (isset($request->org_data['locale'])) {
                    $locale = $request->org_data['locale'];
                } else if (isset($request->user_settings['locale'])) {
                    $locale = $request->user_settings['locale'];
                }
            }

            if (isset($locale) && !empty($locale)) {
                if (Locale::where('locale', $locale)->count()) {
                    \LaravelLocalization::setLocale($locale);
                } else {
                    return ApiController::errorResponse(__('custom.language') . ' ' . "'" . $locale . "'" . ' ' . __('custom.not_exist_database'));
                }
            }
        } else {
            \LaravelLocalization::setLocale(\Session::get('locale'));
        }

        return $next($request);
    }
}
