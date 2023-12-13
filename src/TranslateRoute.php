<?php

namespace Codedor\TranslatableRoutes;

use Codedor\LocaleCollection\Facades\LocaleCollection;
use Codedor\LocaleCollection\Locale;
use Codedor\LocaleCollection\LocaleCollection as TranslatableRoutesLocaleCollection;
use Codedor\TranslatableRoutes\Facades\TranslateRouteParts;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

class TranslateRoute
{
    private static array $errorMessages = [];

    public static function forName(string $routeName, ?string $locale = null, array|Collection $parameters = []): ?string
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
        } catch (Throwable $th) {
            // We don't want to report the same error multiple times
            if (! in_array($th->getMessage(), self::$errorMessages)) {
                self::$errorMessages[] = $th->getMessage();
                report($th);
            }

            return '#';
        }
    }

    public static function getAllForNameOrCurrent(?string $routeName = null, array $parameters = [], ?string $fallbackRoute = null): TranslatableRoutesLocaleCollection
    {
        if (! $routeName) {
            $routeName = request()->route()?->getName();

            $locale = LocaleCollection::firstLocale(app()->getLocale());
            if (Str::startsWith($routeName, $locale->routePrefix() . '.')) {
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
