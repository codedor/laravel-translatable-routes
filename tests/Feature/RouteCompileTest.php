<?php

use Codedor\TranslatableRoutes\Tests\CustomRoutesTestCase;

use Codedor\LocaleCollection\Facades\LocaleCollection;
use Codedor\LocaleCollection\Locale;
use Codedor\TranslatableRoutes\Tests\TestEnums\TestCategory;
use Codedor\TranslatableRoutes\Tests\TestModels\TestPage;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    LocaleCollection::push(new Locale('nl', 'http://codedor.be'))
        ->push(new Locale('fr-BE', 'http://codedor.be', 'fr'))
        ->push(new Locale('en-GB', 'http://codedor.com'));

    LocaleCollection::registerRoutes(function () {
        Route::get('', function () {
            return translated_routes();
        })->name('home');

        Route::get('/page/{page:slug}', function () {
            return translated_routes();
        })->name('page');

        Route::get('/category/{category}', function (TestCategory $category) {
            return translated_routes();
        })->name('enum');
    });

    $this->page = TestPage::first();
});

// calling route:cache does not work in tests, so we have to test the compiled routes manually
it('can compile routes', function () {
    $routes = tap(app('router')->getRoutes(), function ($routes) {
        $routes->refreshNameLookups();
        $routes->refreshActionLookups();
    });

    foreach ($routes as $route) {
        $route->prepareForSerialization();
    }

    expect($routes->compile())->toBeArray();
});
