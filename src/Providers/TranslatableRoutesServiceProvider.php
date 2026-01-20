<?php

namespace Wotz\TranslatableRoutes\Providers;

use Closure;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wotz\LocaleCollection\Locale;
use Wotz\LocaleCollection\LocaleCollection;
use Wotz\TranslatableRoutes\TranslateRoute;
use Wotz\TranslatableRoutes\TranslateRouteParts;
use Wotz\TranslatableRoutes\View\Components\HrefLangTags;

class TranslatableRoutesServiceProvider extends PackageServiceProvider
{
    protected array $bladeComponents = [
        'href-lang-tags' => HrefLangTags::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-translatable-routes')
            ->setBasePath(__DIR__ . '/../')
            ->hasViews('laravel-translatable-routes');
    }

    public function bootingPackage()
    {
        Locale::macro('routePrefix', function (): string {
            /** @var Locale $this */
            return Str::lower($this->locale());
        });

        LocaleCollection::macro('registerRoutes', function (Closure|array|string $callback): void {
            /** @var LocaleCollection $this */
            $this->each(fn (Locale $locale) => Route::middleware('translatable')
                ->domain($locale->url())
                ->where(['translatable_prefix' => $locale->routePrefix()])
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

    public function packageBooted(): void
    {
        parent::packageBooted();

        $this->registerBladeComponents();

        $this->app->bind(TranslateRouteParts::class, function () {
            return new TranslateRouteParts;
        });
    }

    protected function registerBladeComponents()
    {
        foreach ($this->bladeComponents as $view => $class) {
            Blade::component($class, "translatable-routes::{$view}");
        }
    }
}
