<?php

namespace Wotz\TranslatableRoutes;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;
use Wotz\LocaleCollection\LocaleCollection as TranslatableRoutesLocaleCollection;
use Wotz\TranslatableRoutes\Facades\TranslateRouteParts;

class TranslateRoute
{
    public static function forName(string $routeName, ?string $locale = null, array|Collection $parameters = []): ?string
    {
        if (! $locale) {
            $locale = app()->getLocale();
        }

        $localeObject = LocaleCollection::firstLocale($locale);

        try {
            app('url')->forceRootUrl($localeObject->url());

            return route(
                "{$localeObject->routePrefix()}.{$routeName}",
                self::translateParameters($parameters, $localeObject)
            );
        } catch (Throwable $th) {
            return '#';
        }
    }

    public static function getAllForNameOrCurrent(?string $routeName = null, array $parameters = [], ?string $fallbackRoute = null): TranslatableRoutesLocaleCollection
    {
        if (! $routeName) {
            $routeName = request()->route()?->getName();

            $locale = LocaleCollection::firstLocale(app()->getLocale());

            if ($routeName && Str::startsWith($routeName, $locale->routePrefix() . '.')) {
                $routeName = (string) Str::of($routeName)->after($locale->routePrefix() . '.');
            }
        }

        if (! $routeName) {
            $routeName = $fallbackRoute;
        }

        if (! $parameters) {
            $parameters = request()->route()?->parameters() ?? [];
        }

        return LocaleCollection::mapWithKeys(fn (Locale $locale) => [
            $locale->locale() => $routeName
                ? translate_route(
                    $routeName,
                    $locale->locale(),
                    self::translateParameters($parameters, $locale)
                )
                : '#',
        ]);
    }

    public static function translateParts(string $uri, string $locale)
    {
        $parts = explode('/', $uri);

        $translatedUri = [];
        foreach ($parts as $part) {
            if (Str::startsWith($part, '{') && Str::endsWith($part, '}')) {
                $translatedUri[] = $part;

                continue;
            }

            $key = "routes.$part";
            TranslateRouteParts::put($key, $key);

            if (app('translator')->has($key, $locale)) {
                $translatedUri[] = app('translator')->get($key, [], $locale);
            } else {
                $translatedUri[] = $part;
            }
        }

        return implode('/', $translatedUri);
    }

    private static function translateParameters(array $parameters, Locale $locale): array
    {
        return array_map(function ($parameter) use ($locale) {
            if (! ($parameter instanceof Model) || ! method_exists($parameter, 'setLocale')) {
                return $parameter;
            }

            return $parameter->setLocale($locale->locale());
        }, $parameters);
    }
}
