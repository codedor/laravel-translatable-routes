<?php

use Codedor\TranslatableRoutes\Facades\LocaleCollection as FacadesLocaleCollection;
use Codedor\TranslatableRoutes\Locale;
use Codedor\TranslatableRoutes\LocaleCollection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    $this->nlBeLocale = new Locale('nl-BE');
    $this->frBeLocale = new Locale('fr-BE');

    $this->collection = new LocaleCollection();
    $this->collection->push($this->nlBeLocale, $this->frBeLocale);
});

it('can return a current locale', function () {
    app()->setLocale('fr-BE');

    expect($this->collection)
        ->getCurrent()->toEqual($this->frBeLocale);
});

it('can return a fallback locale when a fallback has been set already', function () {
    expect($this->collection)
        ->fallback()->toEqual($this->nlBeLocale)
        ->fallback()->toEqual($this->nlBeLocale);
});

it('can return a fallback locale when cookie is set', function () {
    cookie('locale', 'nl-BE');

    expect($this->collection)
        ->fallback()->toEqual($this->nlBeLocale);
});

it('can return a fallback locale when cookie is set with a non existing locale', function () {
    cookie('locale', 'nl-non-existing');

    expect($this->collection)
        ->fallback()->locale()->not->toEqual('nl-non-existing');
});

it('can return a fallback locale for a browser locale with country', function () {
    mockPreferredBrowserLocale('fr_BE');
    expect($this->collection)
        ->fallback()->locale()->toEqual('fr-BE');
});

it('can return a fallback locale for a browser locale', function () {
    mockPreferredBrowserLocale('fr');
    expect($this->collection)
        ->fallback()->locale()->toEqual('fr-BE');
});

it('can return a fallback locale for a browser locale that does not exists', function () {
    mockPreferredBrowserLocale('non-existing');
    expect($this->collection)
        ->fallback()->locale()->not->toEqual('nl-non-existing');
});

it('can return a fallback locale for the app.fallback_locale config', function () {
    config(['app.fallback_locale' => 'fr-BE']);

    expect($this->collection)
        ->fallback()->locale()->toEqual('fr-BE');
});

it('can return a fallback locale for the first locale')
    ->expect(fn () => $this->collection)
    ->fallback()->locale()->toEqual('nl-BE');

it('can set the current locale', function () {
    $this->collection->setCurrent('nl-BE', 'http://localhost');

    expect(app()->getLocale())
        ->toEqual('nl-BE');

    $this->collection->setCurrent('fr-BE', 'http://localhost');

    expect(app()->getLocale())
        ->toEqual('fr-BE');
});

it('throws error when setting a non-existing locale', function () {
    $this->collection->setCurrent('nl-non-existing', 'http://localhost');
})->throws(NotFoundHttpException::class);

it('can validate the locale')
    ->expect(fn () => $this->collection)
    ->isAllowed('nl-BE')->toBeTrue()
    ->isAllowed('en-BE')->toBefalse();

it('can return the first item for a given value', function () {
    expect($this->collection)
        ->firstLocale('nl-BE')->toEqual($this->nlBeLocale);
});

it('will return nothing as first item for a given locale')
    ->expect(fn () => $this->collection)
    ->firstLocale('en-BE')->toBeNull();

it('will return a first item for a given locale with url', function () {
    expect($this->collection)
        ->firstLocaleWithUrl('nl-BE', 'http://localhost')->toEqual($this->nlBeLocale);
});

it('will return nothing for a given locale with url')
    ->expect(fn () => $this->collection)
    ->firstLocaleWithUrl('nl-BE', 'http://non-existing.test')->toBeNull();

function mockPreferredBrowserLocale($locale, ...$locales)
{
    app()->instance('request', Request::create(
        '/', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => $locale
        ]
    ));
}
