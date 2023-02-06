<?php

namespace Codedor\TranslatableRoutes\Http\Middleware;

use Codedor\TranslatableRoutes\Facades\LocaleCollection;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        LocaleCollection::setCurrent($request->segment(1), $request->root());

        return $next($request);
    }
}
