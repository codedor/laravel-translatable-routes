<?php

namespace Codedor\TranslatableRoutes;

use Closure;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * @template TKey of array-key
 * @template TValue of \Codedor\TranslatableRoutes\Locale
 *
 * @extends \Illuminate\Support\Collection<TKey, TValue>
 */
class LocaleCollection extends Collection
{
    public function getCurrent(): ?Locale
    {
        return $this->firstLocale(app()->currentLocale());
    }

    public function fallback(): Locale
    {
        // If the user has a preferred locale cookie, use that
        if (Cookie::has('locale') && $this->isAllowed(Cookie::get('locale'))) {
            return $this->firstLocale(Cookie::get('locale'));
        }

        // first get locales for current url
        $locales = $this->where(fn (Locale $locale) => $locale->url() === request()->root());

        $preferredBrowserLocale = request()->getPreferredLanguage();

        // if we have a matching browser locale with country (e.g. nl-BE)
        // else if we have a matching browser locale without country (e.g. nl)
        // else if we have a matching browser locale that starts with the preferred browser locale
        // else if we have a matching preferred browser locale that starts with the browser locale
        // else if there is a fallback locale (config('app.fallback_locale))
        // else return first available locale
        return $locales->firstWhere(fn (Locale $locale) => $locale->browserLocaleWithCountry() === $preferredBrowserLocale) ?:
            $locales->firstWhere(fn (Locale $locale) => $locale->browserLocale() === $preferredBrowserLocale) ?:
            $locales->firstWhere(fn (Locale $locale) => Str::startsWith($preferredBrowserLocale, $locale->browserLocaleWithCountry())) ?:
            $locales->firstWhere(fn (Locale $locale) => Str::startsWith($locale->browserLocaleWithCountry(), $preferredBrowserLocale)) ?:
            (app()->getFallbackLocale() ? $locales->firstLocale(app()->getFallbackLocale()) : null) ?:
            $locales->first();
    }

    public function setCurrent(string $currentLocale, string $url): self
    {
        $localeObject = $this->firstLocaleWithUrl($currentLocale, $url);

        abort_if(! $localeObject, 404);

        app()->setLocale($localeObject->locale());

        return $this;
    }

    public function isAllowed(string $localeToFind): bool
    {
        return $this->contains(fn (Locale $locale) => $locale->locale() === $localeToFind);
    }

    public function firstLocale(string $localeToFind): ?Locale
    {
        return $this->firstWhere(fn (Locale $locale) => $locale->locale() === $localeToFind);
    }

    public function firstLocaleWithUrl(string $localeToFind, string $url): ?Locale
    {
        return $this->firstWhere(fn (Locale $locale) => $locale->urlWithLocale() === app('url')->format($url, $localeToFind));
    }

    public function registerRoutes(Closure|array|string $callback): void
    {
        $this->each(fn (Locale $locale) => Route::middleware('translatable')
            ->domain($locale->url())
            ->prefix('/' . $locale->urlLocale())
            ->as($locale->routePrefix() . '.')
            ->group($callback)
        );

        collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RoutingRoute $route) => in_array('translatable', $route->middleware()))
            ->each(function (RoutingRoute $route) {
                $locale = LocaleCollection::firstWhere(fn (Locale $locale) => Str::startsWith($route->getName(), $locale->routePrefix()));

                $route->uri = TranslateRoute::translateParts($route->uri, $locale->locale());
            });
    }
}
