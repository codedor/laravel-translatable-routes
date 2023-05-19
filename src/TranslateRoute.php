<?php

namespace Codedor\TranslatableRoutes;

use Codedor\LocaleCollection\Facades\LocaleCollection;
use Codedor\LocaleCollection\Locale;
use Codedor\LocaleCollection\LocaleCollection as TranslatableRoutesLocaleCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranslateRoute
{
    public static function forName(string $routeName, string $locale = null, array|Collection $parameters = []): ?string
    {
        if (! $locale) {
            $locale = app()->getLocale();
        }

        $localeObject = LocaleCollection::firstLocale($locale);

        app('url')->forceRootUrl($localeObject->url());

        try {
            return route(
                "{$localeObject->routePrefix()}.{$routeName}",
                self::translateParameters($parameters, $localeObject)
            );
        } catch (\Throwable $th) {
            report($th);
            return null;
        }
    }

    public static function getAllForNameOrCurrent(string $routeName = null, array $parameters = []): TranslatableRoutesLocaleCollection
    {
        if (! $routeName) {
            $name = request()->route()->getName();
            $routeName = (string) Str::of($name)->after('.')->after('.');
        }

        if (! $parameters) {
            $parameters = request()->route()->parameters();
        }

        return LocaleCollection::map(fn (Locale $locale) => translate_route(
            $routeName,
            $locale->locale(),
            self::translateParameters($parameters, $locale)
        ));
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
            if (! ($parameter instanceof Model) && ! method_exists($parameter, 'setLocale')) {
                return $parameter;
            }

            return $parameter->setLocale($locale->locale());
        }, $parameters);
    }
}
