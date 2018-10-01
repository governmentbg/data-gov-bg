<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTool
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
        if ($request->segment(1) != 'lang') {
            $isToolPath = $request->segment(1) == 'tool';

            if (env('IS_TOOL')) {
                if (!$isToolPath) {
                    return redirect('tool');
                }
            } else {
                if ($isToolPath) {
                    return redirect('/');
                }
            }
        }

        return $next($request);
    }
}
