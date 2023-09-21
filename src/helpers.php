<?php

use Codedor\LocaleCollection\LocaleCollection;
use Codedor\TranslatableRoutes\TranslateRoute;
use Illuminate\Support\Collection;

if (! function_exists('translate_route')) {
    function translate_route(string $routeName, string $locale = null, array|Collection $parameters = []): ?string
    {
        return TranslateRoute::forName($routeName, $locale, $parameters);
    }
}

if (! function_exists('translated_routes')) {
    function translated_routes(?string $routeName = null, array $parameters = [], ?string $fallbackRoute = null): LocaleCollection
    {
        return TranslateRoute::getAllForNameOrCurrent($routeName, $parameters, $fallbackRoute);
    }
}
