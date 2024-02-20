<?php

namespace Codedor\TranslatableRoutes\Http\Middleware;

use Closure;
use Codedor\LocaleCollection\Facades\LocaleCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        if (is_filament_livewire_route($request)) {
            return $next($request);
        }

        $locale = Str::after($request->route()?->getPrefix(), '/');

        if (is_livewire_route($request)) {
            $snapshot = json_decode($request->json('components.0.snapshot', ''), true);
            $locale = $snapshot['memo']['locale'] ?? null;
        }

        if (is_null($locale)) {
            return $next($request);
        }

        LocaleCollection::setCurrent($locale, $request->root());

        return $next($request);
    }
}
