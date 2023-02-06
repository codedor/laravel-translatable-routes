<?php

use Codedor\TranslatableRoutes\Facades\LocaleCollection as FacadesLocaleCollection;
use Codedor\TranslatableRoutes\Locale;
use Codedor\TranslatableRoutes\LocaleCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    $this->nlBeLocale = new Locale('nl-BE');
    $this->frBeLocale = new Locale('fr-BE');
});

it('can return a current locale', function () {
    app()->setLocale('fr-BE');

    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals($this->frBeLocale, $collection->getCurrent());
});

it('can return a fallback locale when cookie is set', function () {
    cookie('locale', 'nl-BE');
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals($this->nlBeLocale, $collection->fallback());
});

it('can return a fallback locale when cookie is set with a non existing locale', function () {
    cookie('locale', 'nl-non-existing');

    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertNotEquals('nl-non-existing', $collection->fallback()->locale());
});

it('can return a fallback locale for a browser locale with country', function () {
    $mock = FacadesLocaleCollection::partialMock()
        ->shouldReceive('preferredBrowserLocale')
        ->andReturn('fr_BE')
        ->getMock();

    $mock->add($this->nlBeLocale);
    $mock->add($this->frBeLocale);

    $this->assertEquals('fr-BE', $mock->fallback()->locale());
});

it('can return a fallback locale for a browser locale', function () {
    $mock = FacadesLocaleCollection::partialMock()
        ->shouldReceive('preferredBrowserLocale')
        ->andReturn('fr')
        ->getMock();

    $mock->add($this->nlBeLocale);
    $mock->add($this->frBeLocale);

    $this->assertEquals('fr-BE', $mock->fallback()->locale());
});

it('can return a fallback locale for a browser locale that does not exists', function () {
    $mock = FacadesLocaleCollection::partialMock()
        ->shouldReceive('preferredBrowserLocale')
        ->andReturn('non-existing')
        ->getMock();

    $mock->add($this->nlBeLocale);
    $mock->add($this->frBeLocale);

    $this->assertNotEquals('nl-non-existing', $mock->fallback()->locale());
});

it('can return a fallback locale for the app.fallback_locale config', function () {
    config(['app.fallback_locale' => 'fr-BE']);

    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals('fr-BE', $collection->fallback()->locale());
});

it('can return a fallback locale for the first locale', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals('nl-BE', $collection->fallback()->locale());
});

it('can set the current locale', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $collection->setCurrent('nl-BE', 'http://localhost');

    $this->assertEquals('nl-BE', app()->getLocale());

    $collection->setCurrent('fr-BE', 'http://localhost');

    $this->assertEquals('fr-BE', app()->getLocale());
});

it('throws error when setting a non-existing locale', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $collection->setCurrent('nl-non-existing', 'http://localhost');
})->throws(NotFoundHttpException::class);

it('can validate the locale', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertTrue($collection->isAllowed('nl-BE'));
    $this->assertFalse($collection->isAllowed('en-BE'));
});

it('can return the first item for a given value', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals($collection->firstLocale('nl-BE'), $this->nlBeLocale);
});

it('will return nothing as first item for a given locale', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals($collection->firstLocale('en-BE'), null);
});

it('will return a first item for a given locale with url', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals($collection->firstLocaleWithUrl('nl-BE', 'http://localhost'), $this->nlBeLocale);
});

it('will return nothing for a given locale with url', function () {
    $collection = new LocaleCollection();
    $collection->add($this->nlBeLocale);
    $collection->add($this->frBeLocale);

    $this->assertEquals($collection->firstLocaleWithUrl('nl-BE', 'http://non-existing.test'), null);
});
