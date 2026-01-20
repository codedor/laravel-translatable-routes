<?php

use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;
use Wotz\TranslatableRoutes\Http\Middleware\SetLocale;

beforeEach(function () {
    LocaleCollection::push(new Locale('nl'))
        ->push(new Locale('fr-BE', null, 'fr'))
        ->push(new Locale('en-GB', null, 'en'));
});

it('sets the correct locale', function () {
    LocaleCollection::registerRoutes(function () {
        Route::get('', function () {
            return translated_routes();
        })->name('home');
    });

    createRequestAndHandleMiddleware('/en', true);

    expect(LocaleCollection::getCurrent())
        ->locale()->toBe('en-GB');
});

it('throws 404 when locale is not valid', function () {
    createRequestAndHandleMiddleware('/not-allowed');

    expect(LocaleCollection::getCurrent())
        ->toBeNull();
});

it('throws 404 when url is not found', function () {
    createRequestAndHandleMiddleware('http://whoownsthezebra.be/en');

    expect(LocaleCollection::getCurrent())
        ->toBeNull();
});

function createRequestAndHandleMiddleware(string $url, bool $setRouteResolver = false)
{
    $request = Request::create($url);
    $request = HttpRequest::createFromBase($request);

    if ($setRouteResolver) {
        $request->setRouteResolver(fn () => Route::getRoutes()->match($request));
    }

    return (new SetLocale)->handle($request, fn () => new Response);
}
