<?php

namespace Codedor\TranslatableRoutes\Providers;

use Codedor\TranslatableRoutes\LocaleCollection;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TranslatableRoutesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-translatable-routes')
            ->setBasePath(__DIR__ . '/../')
            ->hasConfigFile()
            ->hasMigration('create_package_table');
    }

    public function registeringPackage()
    {
        $this->app->singleton(LocaleCollection::class, function () {
            return new LocaleCollection();
        });
    }
}
