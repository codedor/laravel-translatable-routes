<?php

namespace Wotz\TranslatableRoutes\Http\Controllers;

use Wotz\LocaleCollection\Facades\LocaleCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectToLocaleController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->session()->reflash();

        return redirect(LocaleCollection::fallback()->urlWithLocale());
    }
}
