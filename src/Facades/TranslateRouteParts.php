<?php

namespace Codedor\TranslatableRoutes\Facades;

use Illuminate\Support\Facades\Facade;

class TranslateRouteParts extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Codedor\TranslatableRoutes\TranslateRouteParts::class;
    }
}
