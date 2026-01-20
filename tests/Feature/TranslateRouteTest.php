<?php

use Illuminate\Support\Facades\Route;
use Wotz\LocaleCollection\Facades\LocaleCollection;
use Wotz\LocaleCollection\Locale;
use Wotz\TranslatableRoutes\Tests\TestEnums\TestCategory;
use Wotz\TranslatableRoutes\Tests\TestModels\TestPage;

beforeEach(function () {
    LocaleCollection::push(new Locale('nl', 'http://whoownsthezebra.be'))
        ->push(new Locale('fr-BE', 'http://whoownsthezebra.be', 'fr'))
        ->push(new Locale('en-GB', 'http://whoownsthezebra.com'));

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

it('can translate a route', function ($data, $url) {
    expect(translate_route(...$data))
        ->toEqual($url);
})
    ->with([
        'home - fr-BE' => [
            ['home', 'fr-BE'],
            'http://whoownsthezebra.be/fr',
        ],
        'home - nl' => [
            ['home', 'nl'],
            'http://whoownsthezebra.be/nl',
        ],
        'home - en-GB' => [
            ['home', 'en-GB'],
            'http://whoownsthezebra.com/en-GB',
        ],
        'page - fr-BE' => fn () => [
            ['page', 'fr-BE', [$this->page]],
            'http://whoownsthezebra.be/fr/page/fr-slug',
        ],
        'page - nl' => fn () => [
            ['page', 'nl', [$this->page]],
            'http://whoownsthezebra.be/nl/pagina/nl-slug',
        ],
        'page - en-GB' => fn () => [
            ['page', 'en-GB', [$this->page]],
            'http://whoownsthezebra.com/en-GB/page/en-slug',
        ],
        'enum - fr-BE' => fn () => [
            ['enum', 'fr-BE', [TestCategory::Fruits]],
            'http://whoownsthezebra.be/fr/category/fruits',
        ],
        'enum - nl' => fn () => [
            ['enum', 'nl', [TestCategory::Fruits]],
            'http://whoownsthezebra.be/nl/category/fruits',
        ],
        'enum - en-GB' => fn () => [
            ['enum', 'en-GB', [TestCategory::Fruits]],
            'http://whoownsthezebra.com/en-GB/category/fruits',
        ],
    ]);

it('can translate a route and falls back to app locale', function () {
    app()->setLocale('en-GB');

    expect(translate_route('home'))
        ->toEqual('http://whoownsthezebra.com/en-GB');
});

it('can return translated routes')
    ->expect(fn () => translated_routes('page', [TestPage::first()])->toArray())
    ->toMatchArray([
        'nl' => 'http://whoownsthezebra.be/nl/pagina/nl-slug',
        'fr-BE' => 'http://whoownsthezebra.be/fr/page/fr-slug',
        'en-GB' => 'http://whoownsthezebra.com/en-GB/page/en-slug',
    ]);

it('can return translated routes for current route', function () {
    app()->setLocale('nl');
    $this->get(translate_route('home'))
        ->assertJson([
            'nl' => 'http://whoownsthezebra.be/nl',
            'fr-BE' => 'http://whoownsthezebra.be/fr',
            'en-GB' => 'http://whoownsthezebra.com/en-GB',
        ]);
});
