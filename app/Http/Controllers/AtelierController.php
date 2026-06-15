<?php

namespace App\Http\Controllers;

use App\Models\Showcase;

class AtelierController extends Controller
{
    public function index()
    {
        $showcases = Showcase::active()->orderBy('sort_order')->get();

        return view('atelier.index', compact('showcases'));
    }
}
