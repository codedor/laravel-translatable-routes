<?php

use Codedor\LocaleCollection\LocaleCollection;
use Codedor\TranslatableRoutes\TranslateRoute;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

if (! function_exists('translate_route')) {
    function translate_route(string $routeName, string $locale = null, array|Collection $parameters = []): ?string
    {
        return TranslateRoute::forName($routeName, $locale, $parameters);
    }
}

if (! function_exists('translated_routes')) {
    function translated_routes(string $routeName = null, array $parameters = [], string $fallbackRoute = null): LocaleCollection
    {
        return TranslateRoute::getAllForNameOrCurrent($routeName, $parameters, $fallbackRoute);
    }
}

if (! function_exists('is_filament_livewire_route')) {
    function is_filament_livewire_route($request): bool
    {
        if ($request->headers->has('X-LIVEWIRE') && $request->server('HTTP_REFERER')) {
            $referer = $request->server('HTTP_REFERER');
            $isFilament = collect(Filament::getPanels())
                ->contains(fn (Panel $panel) => Str::startsWith($referer, $panel->getUrl()));

            if ($isFilament) {
                return true;
            }
        }

        return false;
    }
}
