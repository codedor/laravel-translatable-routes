<?php

namespace Codedor\TranslatableRoutes\Http\Middleware;

use Closure;
use Codedor\LocaleCollection\Facades\LocaleCollection;
use Filament\Facades\Filament;
use Filament\Panel;
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

        $locale = $request->segment(1);

        if ($request->headers->has('X-LIVEWIRE')) {
            $snapshot = json_decode($request->json('components.0.snapshot', []), true);

            if (isset($snapshot['memo']['locale'])) {
                $locale = $snapshot['memo']['locale'];
            }
        }

        if (! $locale) {
            return $next($request);
        }

        LocaleCollection::setCurrent($locale, $request->root());

        return $next($request);
    }
}
