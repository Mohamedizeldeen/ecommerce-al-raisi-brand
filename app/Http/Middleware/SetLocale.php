<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the visitor's chosen language (session) to the app for this request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('regions.default_locale', 'en'));

        if (array_key_exists($locale, config('regions.locales', []))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
