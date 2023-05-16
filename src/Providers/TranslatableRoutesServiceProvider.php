<?php

namespace Codedor\TranslatableRoutes\Providers;

use Closure;
use Codedor\LocaleCollection\Locale;
use Codedor\LocaleCollection\LocaleCollection;
use Codedor\TranslatableRoutes\TranslateRoute;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TranslatableRoutesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-translatable-routes')
            ->setBasePath(__DIR__ . '/../');
    }

    public function bootingPackage()
    {
        Locale::macro('host', function (): string {
            /** @var Locale $this */
            return parse_url($this->url(), PHP_URL_HOST);
        });

        Locale::macro('routePrefix', function (): string {
            /** @var Locale $this */
            return Str::lower($this->locale() . '.' . Str::slug($this->host()));
        });

        LocaleCollection::macro('registerRoutes', function (Closure|array|string $callback): void {
            /** @var LocaleCollection $this */
            $this->each(fn (Locale $locale) => Route::middleware('translatable')
                ->domain($locale->url())
                // ->where(['locale' => $locale->locale()])
                ->prefix('/' . $locale->urlLocale())
                ->as($locale->routePrefix() . '.')
                ->group($callback)
            );

            collect(Route::getRoutes()->getRoutes())
                ->filter(fn (RoutingRoute $route) => in_array('translatable', $route->middleware()))
                ->each(function (RoutingRoute $route) {
                    $locale = $this->firstWhere(fn (Locale $locale) => Str::startsWith($route->getName(), $locale->routePrefix()));

                    if (! $locale) {
                        return;
                    }

                    $route->uri = TranslateRoute::translateParts($route->uri, $locale->locale());
                });
        });
    }
}
