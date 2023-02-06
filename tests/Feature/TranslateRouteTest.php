<?php

use Codedor\TranslatableRoutes\Facades\LocaleCollection;
use Codedor\TranslatableRoutes\Locale;
use Codedor\TranslatableRoutes\Tests\TestModels\TestPage;
use Codedor\TranslatableRoutes\TranslateRoute;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function () {
    LocaleCollection::add(new Locale('nl', 'codedor.be'))
        ->add(new Locale('fr-BE', 'codedor.be', 'fr'))
        ->add(new Locale('en-GB', 'codedor.com'));

    LocaleCollection::each(function (Locale $locale) {
        Route::middleware('translatable')
            ->domain($locale->url()) // Make sure the configured domain is enforced
            ->prefix('/' . $locale->urlLocale())
            ->as($locale->routePrefix() . '.')
            ->group(function () {
                Route::get('', function () {
                    return app()->getLocale();
                })->name('home');

                Route::get('/page/{page:slug}', function () {
                    return app()->getLocale();
                })->name('page');
            });
    });

    collect(Route::getRoutes()->getRoutes())->map(function (RoutingRoute $route) {
        if (! in_array('translatable', $route->middleware())) {
            return $route;
        }

        $locale = LocaleCollection::firstWhere(function (Locale $locale) use ($route) {
            return Str::startsWith($route->getName(), $locale->routePrefix());
        });

        $route->uri = TranslateRoute::translateParts($route->uri, $locale->locale());

        return $route;
    });
});

it('can translate a route', function () {
    $this->assertEquals(TranslateRoute::forName('home', 'fr-BE'), 'http://codedor.be/fr');
    $this->assertEquals(TranslateRoute::forName('home', 'nl'), 'http://codedor.be/nl');
    $this->assertEquals(TranslateRoute::forName('home', 'en-GB'), 'http://codedor.com/en-GB');

    $page = TestPage::first();
    $this->assertEquals(TranslateRoute::forName('page', 'fr-BE', [$page]), 'http://codedor.be/fr/page/fr-slug');
    $this->assertEquals(TranslateRoute::forName('page', 'nl', [$page]), 'http://codedor.be/nl/pagina/nl-slug');
    $this->assertEquals(TranslateRoute::forName('page', 'en-GB', [$page]), 'http://codedor.com/en-GB/page/en-slug');
});

it('can return translated routes', function () {
    $this->assertEquals(TranslateRoute::getAllForNameOrCurrent('page', [TestPage::first()])->toArray(), [
        'http://codedor.be/nl/pagina/nl-slug',
        'http://codedor.be/fr/page/fr-slug',
        'http://codedor.com/en-GB/page/en-slug',
    ]);
});
