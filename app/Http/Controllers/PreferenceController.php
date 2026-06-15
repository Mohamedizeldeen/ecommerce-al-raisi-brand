<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PreferenceController extends Controller
{
    /**
     * Store the visitor's language and/or currency choice in the session.
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'locale' => ['nullable', Rule::in(array_keys(config('regions.locales')))],
            'currency' => ['nullable', Rule::in(array_keys(config('regions.currencies')))],
        ]);

        if (! empty($data['locale'])) {
            session(['locale' => $data['locale']]);
        }

        if (! empty($data['currency'])) {
            session(['currency' => $data['currency']]);
        }

        return back();
    }
}
