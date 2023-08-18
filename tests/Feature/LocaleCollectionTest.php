<?php

use Codedor\LocaleCollection\Locale;
use Codedor\LocaleCollection\LocaleCollection;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->nlBeLocale = new Locale('nl-BE');
    $this->frBeLocale = new Locale('fr-BE');

    $this->collection = new LocaleCollection();
    $this->collection->push($this->nlBeLocale, $this->frBeLocale);

    Route::get('non-translatable', function () {
        return translated_routes();
    })->middleware('translatable')->name('non-translatable');

    $this->collection->registerRoutes(function () {
        Route::get('', function () {
            return translated_routes();
        })->name('home');
    });
});

it('will only prefix the translatable routes name with the locale url prefix', function () {
    expect(Route::getRoutes()->getRoutes())
        ->sequence(
            fn ($route) => $route
                ->getName()->toBe('non-translatable')
                ->wheres->toBe([]),
            fn ($route) => $route
                ->getName()->toBe('nl-be.home'),
            fn ($route) => $route
                ->getName()->toBe('fr-be.home')
        );
});
