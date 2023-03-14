<?php

use Codedor\TranslatableRoutes\Locale;

it('can return a locale')
    ->expect(fn () => new Locale('nl-BE'))
    ->locale()->toEqual('nl-BE');

it('can return an url')
    ->expect(fn () => new Locale('nl-BE', 'http://localhost'))
    ->url()->toEqual('http://localhost');

it('can return a default url', function () {
    config(['app.url' => 'http://default.test']);

    expect(new Locale('nl-BE'))
        ->url()->toEqual('http://default.test');
});

it('can return an url locale')
    ->expect(fn () => new Locale('nl-BE', 'http://localhost', 'nl'))
    ->urlLocale()->toEqual('nl');

it('falls back to the locale if no url locale is passed')
    ->expect(fn () => new Locale('nl-BE'))
    ->urlLocale()->toEqual('nl-BE');

it('can return an url with locale')
    ->expect(fn () => new Locale('nl-BE', 'http://localhost', 'nl'))
    ->urlWithLocale()->toEqual('http://localhost/nl');

it('can return a browser locale for locale with country')
    ->expect(fn () => new Locale('nl-BE'))
    ->browserLocale()->toEqual('nl');

it('can return a browser locale')
    ->expect(fn () => new Locale('nl'))
    ->browserLocale()->toEqual('nl');

it('can return a browser locale with country for locale with country')
    ->expect(fn () => new Locale('nl-BE'))
    ->browserLocaleWithCountry()->toEqual('nl_BE');

it('can return a browser locale with country')
    ->expect(fn () => new Locale('nl'))
    ->browserLocaleWithCountry()->toEqual('nl');

it('can return an extra array')
    ->expect(fn () => new Locale('nl', null, null, ['layout' => 'new']))
    ->extras()->toMatchArray(['layout' => 'new']);

it('can return a key for the extra array')
    ->expect(fn () => new Locale('nl', null, null, ['layout' => 'new']))
    ->extras('layout')->toEqual('new');
