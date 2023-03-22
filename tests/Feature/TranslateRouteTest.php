<?php

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

it('can translate a route', function ($data, $url) {
    expect(translate_route(...$data))
        ->toEqual($url);
})
    ->with([
        'home - fr-BE' => [
            ['home', 'fr-BE'],
            'http://codedor.be/fr',
        ],
        'home - nl' => [
            ['home', 'nl'],
            'http://codedor.be/nl',
        ],
        'home - en-GB' => [
            ['home', 'en-GB'],
            'http://codedor.com/en-GB',
        ],
        'page - fr-BE' => fn () => [
            ['page', 'fr-BE', [$this->page]],
            'http://codedor.be/fr/page/fr-slug',
        ],
        'page - nl' => fn () => [
            ['page', 'nl', [$this->page]],
            'http://codedor.be/nl/pagina/nl-slug',
        ],
        'page - en-GB' => fn () => [
            ['page', 'en-GB', [$this->page]],
            'http://codedor.com/en-GB/page/en-slug',
        ],
        'enum - fr-BE' => fn () => [
            ['enum', 'fr-BE', [TestCategory::Fruits]],
            'http://codedor.be/fr/category/fruits',
        ],
        'enum - nl' => fn () => [
            ['enum', 'nl', [TestCategory::Fruits]],
            'http://codedor.be/nl/category/fruits',
        ],
        'enum - en-GB' => fn () => [
            ['enum', 'en-GB', [TestCategory::Fruits]],
            'http://codedor.com/en-GB/category/fruits',
        ],
    ]);

it('can translate a route and falls back to app locale', function () {
    app()->setLocale('en-GB');

    expect(translate_route('home'))
        ->toEqual('http://codedor.com/en-GB');
});

it('can return translated routes')
    ->expect(fn () => translated_routes('page', [TestPage::first()])->toArray())
    ->toMatchArray([
        'http://codedor.be/nl/pagina/nl-slug',
        'http://codedor.be/fr/page/fr-slug',
        'http://codedor.com/en-GB/page/en-slug',
    ]);

it('can return translated routes for current route', function () {
    app()->setLocale('nl');
    $this->get(translate_route('home'))
        ->assertJson([
            'http://codedor.be/nl',
            'http://codedor.be/fr',
            'http://codedor.com/en-GB',
        ]);
});
