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
        if (!config('app.IS_TOOL') && !config('app.IS_TEST_TOOL')) {
            $pages = HelpPage::select()->get();

            $name = $request->getPathInfo();
            $name = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+/', '', $name);
            $name = preg_replace('/[0-9]+/', '', $name);

            foreach ($pages as $page) {
                if ($name == $page->name) {
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

    private static function getSectionFromPage($page)
    {
        $section = $page->section;

        if (!empty($section)) {
            if ($section->parent_id) {
                $section = HelpSection::find($section->parent_id);
            }
            $subsections = $section->subsections()->get();
            $pages = $section->pages()->get();

            return collect([
                'section'       => $section,
                'subsections'   => $subsections,
                'pages'         => $pages,
            ]);
        }

        return $page;
    }
}
