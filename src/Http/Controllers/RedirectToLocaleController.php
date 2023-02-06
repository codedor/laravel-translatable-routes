<?php

namespace Codedor\TranslatableRoutes\Http\Controllers;

use Codedor\TranslatableRoutes\Facades\LocaleCollection;
use Illuminate\Http\Request;

class RedirectToLocaleController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $request->session()->reflash();

        return redirect(LocaleCollection::fallback()->urlWithLocale());
    }
}
