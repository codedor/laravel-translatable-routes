<?php

use Codedor\TranslatableRoutes\Facades\LocaleCollection;
use Codedor\TranslatableRoutes\Http\Controllers\RedirectToLocaleController;
use Codedor\TranslatableRoutes\Locale;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    LocaleCollection::push(new Locale('nl'))
        ->push(new Locale('fr'))
        ->push(new Locale('en'))
        ->push(new Locale('de'));

    Route::middleware('web')->get('', RedirectToLocaleController::class)->name('splash');
});

it('redirects to cookie locale')
    ->withCookies([
        'locale' => 'de',
    ])
    ->get('/')
    ->assertRedirect('/de');

it('redirects to browser locale when no cookie', function () {
    config(['app.fallback_locale' => 'de']);
    $this
        ->withHeaders([
            'Accept-Language' => 'en,en-GB',
        ])
        ->withCookies([
            'locale' => null,
        ])
        ->get(route('splash'))
        ->assertRedirect('/en');
});

it('redirects to browser locale with country when no cookie', function () {
    config(['app.fallback_locale' => 'de']);
    $this
        ->withHeaders([
            'Accept-Language' => 'nl-BE',
        ])
        ->withCookies([
            'locale' => null,
        ])
        ->get(route('splash'))
        ->assertRedirect('/nl');
});

it('redirects to fallback locale when cookie and Accept-Language values are not valid', function () {
    config(['app.fallback_locale' => 'fr']);
    $this
        ->withHeaders([
            'Accept-Language' => 'not-allowed',
        ])
        ->withCookies([
            'locale' => 'not-allowed',
        ])
        ->get(route('splash'))
        ->assertRedirect('/fr');
});

it('redirects to first locale when cookie Accept-Language and fallback values are not valid', function () {
    config(['app.fallback_locale' => 'not-allowed']);
    $this
        ->withHeaders([
            'Accept-Language' => 'not-allowed',
        ])
        ->withCookies([
            'locale' => 'not-allowed',
        ])
        ->get(route('splash'))
        ->assertRedirect('/nl');
});
