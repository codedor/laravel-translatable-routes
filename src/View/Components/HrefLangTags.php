<?php

namespace Codedor\TranslatableRoutes\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HrefLangTags extends Component
{
    public function render(): View|Closure|string
    {
        return view('laravel-translatable-routes::components.href-lang-tags', [
            'routes' => translated_routes()->filter(fn ($route) => ! in_array($route, ['#', '', 'null'])),
            'isHome' => request()->routeIs('*.home'),
        ]);
    }
}
