<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('', RedirectToLocaleController::class)->name('splash');
});
