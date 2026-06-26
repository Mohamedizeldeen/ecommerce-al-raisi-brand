<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Post;
use App\Models\Product;

class SitemapController extends Controller
{
    public function __invoke()
    {
        // Index/static routes — no lastmod (content isn't tracked per-URL here).
        $urls = collect([
            url('/'),
            route('collections.index'),
            route('blog.index'),
            route('press.index'),
            route('atelier'),
            route('contact'),
            route('about'),
            route('size-guide'),
            route('pages.shipping'),
            route('pages.privacy'),
            route('pages.terms'),
        ])->map(fn ($loc) => ['loc' => $loc, 'lastmod' => null]);

        // Content entities carry a lastmod from updated_at so crawlers see freshness.
        $entry = fn ($model, string $loc) => [
            'loc' => $loc,
            'lastmod' => optional($model->updated_at)->toAtomString(),
        ];

        $urls = $urls
            ->merge(Collection::active()->get()->map(fn ($c) => $entry($c, route('collections.show', $c))))
            ->merge(Category::active()->get()->map(fn ($c) => $entry($c, route('categories.show', $c))))
            ->merge(Product::published()->get()->map(fn ($p) => $entry($p, route('products.show', $p))))
            ->merge(Post::published()->get()->map(fn ($p) => $entry(
                $p,
                route($p->type === 'press' ? 'press.show' : 'blog.show', $p)
            )));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '  <url><loc>'.e($url['loc']).'</loc>';
            if (! empty($url['lastmod'])) {
                $xml .= '<lastmod>'.e($url['lastmod']).'</lastmod>';
            }
            $xml .= '</url>'."\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
