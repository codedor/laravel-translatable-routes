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
    private ?Locale $fallbackLocale = null;

    public function getCurrent(): ?Locale
    {
        return $this->firstLocale(app()->currentLocale());
    }

    public function fallback(): Locale
    {
        if ($this->fallbackLocale && self::isAllowed($this->fallbackLocale->locale())) {
            return $this->fallbackLocale;
        }

        // If the user has a preferred locale cookie, use that
        if (Cookie::has('locale') && self::isAllowed(Cookie::get('locale'))) {
            return $this->fallbackLocale = $this->firstLocale(Cookie::get('locale'));
        }

        $preferredBrowserLocale = $this->preferredBrowserLocale();

        // if we have a matching browser locale with country (e.g. nl-BE)
        // else if we have a matching browser locale without country (e.g. nl)
        // else if there is a fallback locale (config('app.fallback_locale))
        // else return first available locale
        return $this->fallbackLocale =
                $this->firstWhere(fn (Locale $locale) => $locale->browserLocaleWithCountry() === $preferredBrowserLocale) ?:
                $this->firstWhere(fn (Locale $locale) => $locale->browserLocale() === $preferredBrowserLocale) ?:
                (app()->getFallbackLocale() ? $this->firstLocale(app()->getFallbackLocale()) : null) ?:
                $this->first();
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

    /**
     * Get the preferred browser locale based on the browser_locale_with_country keys
     */
    public function preferredBrowserLocale(): ?string
    {
        return request()->getPreferredLanguage($this->pluck('browser_locale_with_country')->toArray());
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
