<?php

namespace Wotz\TranslatableRoutes\Facades;

use Illuminate\Support\Facades\Facade;

class TranslateRouteParts extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Wotz\TranslatableRoutes\TranslateRouteParts::class;
    }
}
