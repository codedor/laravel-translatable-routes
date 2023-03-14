<?php

namespace Codedor\TranslatableRoutes\Http\Middleware;

use Closure;
use Codedor\TranslatableRoutes\Facades\LocaleCollection;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        LocaleCollection::setCurrent($request->segment(1), $request->root());

        return $next($request);
    }
}
