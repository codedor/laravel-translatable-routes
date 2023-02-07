<?php

use Codedor\TranslatableRoutes\Facades\LocaleCollection;
use Codedor\TranslatableRoutes\Locale;
use Codedor\TranslatableRoutes\Tests\TestModels\TestPage;
use Codedor\TranslatableRoutes\TranslateRoute;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

beforeEach(function () {
    LocaleCollection::add(new Locale('nl', 'codedor.be'))
        ->add(new Locale('fr-BE', 'codedor.be', 'fr'))
        ->add(new Locale('en-GB', 'codedor.com'));

    LocaleCollection::registerRoutes(function () {
        Route::get('', function () {
            return app()->getLocale();
        })->name('home');

        Route::get('/page/{page:slug}', function () {
            return app()->getLocale();
        })->name('page');
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
    // dd(Route::getRoutes()->getRoutes());
    $this->assertEquals(TranslateRoute::getAllForNameOrCurrent('page', [TestPage::first()])->toArray(), [
        'http://codedor.be/nl/pagina/nl-slug',
        'http://codedor.be/fr/page/fr-slug',
        'http://codedor.com/en-GB/page/en-slug',
    ]);
});
