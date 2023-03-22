# Translatable Routes for Laravel

## Introduction

When it comes to handling translated routes, the process can be complex and time-consuming. That's where the Translatable Routes package for Laravel comes in! This package provides a simple and intuitive way to manage translated routes in your Laravel application. With its fluent API and flexible configuration options, it makes it easy to define and generate translated routes, streamlining the localization process and making it faster and easier for developers to handle translated URLs. Whether you're building a multi-lingual web application or simply need to handle translated routes, the Translatable Routes package for Laravel is a must-have tool.

## Installation

First, install this package via the Composer package manager:

```bash
composer require codedor/laravel-translatable-routes
```

After installing this package, you can create a `routes/translatable.php` file.
This file is like the `web.php` file, but this way we can easily register these routes to be translatable.

Next make a `translatable` group in `app/Http/Kernel.php`

```php
protected $middlewareGroups = [
    // ...

    'translatable' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Codedor\TranslatableRoutes\Http\Middleware\SetLocale::class,
    ],
];
```

This is just a copy of the `web` middleware group, but with `\Codedor\TranslatableRoutes\Http\Middleware\SetLocale::class` middleware as an extra.

In the `AppServiceProvider` (or where you prefer) the Locale can be defined.

```php
use \Codedor\LocaleCollection\Facades\LocaleCollection;
use Codedor\LocaleCollection\Locale;

public function boot()
{
    LocaleCollection::add(new Locale('nl'))
        ->add(new Locale('fr'));
}
```

Then the routes can be registered in the `RouteServiceProvider`.

```php
public function boot()
{
    // ...

    $this->routes(function () {
        // ...

        \Codedor\TranslatableRoutes\Facades\LocaleCollection::registerRoutes(base_path('routes/translatable.php'));
    });
}
```

By adding the routes in a translatable file, we can easily register them.

## Translatable route parts

All parts that are not parameters will be translated.

E.g.

```php
// routes/translatable.php
Route::get('/page/{page:slug}', PageController::class)->name('page');
```

```php
// lang/en/routes.php
return [
    'page' => 'page',
];
```

```php
// lang/nl/routes.php
return [
    'page' => 'pagina',
];
```

This will output for NL and EN:

```
/nl/pagina/{page}
/en/page/{page}
```

## Translatable route parameters

If a parameter is a model and has a `setLocale($locale)` method, the parameter will be translated.
This package has default integration for [Spatie's Translatable package](https://github.com/spatie/laravel-translatable).
