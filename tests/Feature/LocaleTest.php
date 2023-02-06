<?php

use Codedor\TranslatableRoutes\Locale;

it('can return a locale', function () {
    $locale = new Locale('nl-BE');

    $this->assertEquals($locale->locale(), 'nl-BE');
});

it('can return an url', function () {
    $locale = new Locale('nl-BE', 'https://localhost');

    $this->assertEquals($locale->url(), 'https://localhost');
});

it('can return a default url', function () {
    config(['app.url' => 'https://default.test']);
    $locale = new Locale('nl-BE');

    $this->assertEquals($locale->url(), 'https://default.test');
});

it('can return an url locale', function () {
    $locale = new Locale('nl-BE', 'https://localhost', 'nl');

    $this->assertEquals($locale->urlLocale(), 'nl');
});

it('can return a default url locale', function () {
    config(['app.url' => 'https://default.test']);
    $locale = new Locale('nl-BE');

    $this->assertEquals($locale->urlLocale(), 'nl-BE');
});

it('can return an url with locale', function () {
    $locale = new Locale('nl-BE', 'https://localhost', 'nl');

    $this->assertEquals($locale->urlWithLocale(), 'https://localhost/nl');
});

it('can return a browser locale for locale with country', function () {
    $locale = new Locale('nl-BE');

    $this->assertEquals($locale->browserLocale(), 'nl');
});

it('can return a browser locale', function () {
    $locale = new Locale('nl');

    $this->assertEquals($locale->browserLocale(), 'nl');
});

it('can return a browser locale with country for locale with country', function () {
    $locale = new Locale('nl-BE');

    $this->assertEquals($locale->browserLocaleWithCountry(), 'nl_BE');
});

it('can return a browser locale with country', function () {
    $locale = new Locale('nl');

    $this->assertEquals($locale->browserLocaleWithCountry(), 'nl');
});

it('can return an extra array', function () {
    $locale = new Locale('nl', null, null, ['layout' => 'new']);

    $this->assertEquals($locale->extras(), ['layout' => 'new']);
});

it('can return a key for the extra array', function () {
    $locale = new Locale('nl', null, null, ['layout' => 'new']);

    $this->assertEquals($locale->extras('layout'), 'new');
});

it('can return a route prefix', function () {
    $locale = new Locale('nl-BE', 'localhost', 'nl');

    $this->assertEquals($locale->routePrefix(), 'nl-be.localhost');
});
