<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = collect([
            url('/'),
            route('collections.index'),
        ]);

        $urls = $urls
            ->merge(Collection::active()->get()->map(fn ($c) => route('collections.show', $c)))
            ->merge(Category::active()->get()->map(fn ($c) => route('categories.show', $c)))
            ->merge(Product::published()->get()->map(fn ($p) => route('products.show', $p)));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $loc) {
            $xml .= '  <url><loc>'.e($loc).'</loc></url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
