<?php

use Codedor\LocaleCollection\Facades\LocaleCollection;
use Codedor\LocaleCollection\Locale;
use Codedor\TranslatableRoutes\Http\Middleware\SetLocale;
use Illuminate\Http\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    LocaleCollection::push(new Locale('nl'))
        ->push(new Locale('fr-BE', null, 'fr'))
        ->push(new Locale('en-GB', null, 'en'));
});

it('sets the correct locale', function () {
    createRequestAndHandleMiddleware('/en');

    expect(LocaleCollection::getCurrent())
        ->locale()->toBe('en-GB');
});

it('throws 404 when locale is not valid', function () {
    createRequestAndHandleMiddleware('/not-allowed');

    expect(LocaleCollection::getCurrent())
        ->toBeNull();
});

it('throws 404 when url is not found', function () {
    createRequestAndHandleMiddleware('http://codedor.be/en');

    expect(LocaleCollection::getCurrent())
        ->toBeNull();
});

function createRequestAndHandleMiddleware($url)
{
    $request = Request::create($url);
    $request = HttpRequest::createFromBase($request);

    return (new SetLocale())->handle($request, fn () => new Response());
}
