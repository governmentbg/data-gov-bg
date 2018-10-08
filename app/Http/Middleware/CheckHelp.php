<?php

namespace App\Http\Middleware;

use Closure;
use App\HelpPage;
use App\HelpSection;
use Illuminate\Log\Logger;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class CheckHelp
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
        if (!env('IS_TOOL')) {
            $pages = HelpPage::where('active', true)->get();

            foreach ($pages as $page) {
                if ($request->is($page->name)) {
                    view()->share('help', $page);

                    return $next($request);
                } elseif ($request->is($page->name .'/*')) {
                    view()->share('help', $page);

                    return $next($request);
                } else if ($request->is('/') && $page->name == 'home') {
                    view()->share('help', $page);

                    return $next($request);
                }
            }
        }

        return $next($request);
    }
}
