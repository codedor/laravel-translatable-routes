<?php

namespace Codedor\TranslatableRoutes\Providers;

use Closure;
use Codedor\LocaleCollection\Locale;
use Codedor\LocaleCollection\LocaleCollection;
use Codedor\TranslatableRoutes\TranslateRoute;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
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
        LocaleCollection::macro('registerRoutes', function (Closure|array|string $callback): void {
            /** @var LocaleCollection $this */
            $this->each(fn (Locale $locale) => Route::middleware('translatable')
                ->domain($locale->url())
                ->where(['locale' => $locale->locale()])
                ->prefix('/' . $locale->urlLocale())
                ->group($callback)
            );

            collect(Route::getRoutes()->getRoutes())
                ->filter(fn (RoutingRoute $route) => in_array('translatable', $route->middleware()))
                ->each(function (RoutingRoute $route) {
                    $locale = $this->firstLocale($route->wheres['locale'] ?? '');

                    if (! $locale) {
                        return;
                    }

                    $route->uri = TranslateRoute::translateParts($route->uri, $locale->locale());
                });
        });
    }
}
