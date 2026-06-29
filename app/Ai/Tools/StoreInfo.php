<?php

namespace App\Ai\Tools;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Cache;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Returns the store's own support knowledge: contact details, hours, shipping &
 * returns policy, payment, size guide, and the full category/collection list.
 * Every value comes from store settings or the site's published pages, so the
 * assistant can answer support questions without inventing anything.
 */
class StoreInfo implements Tool
{
    public function description(): string
    {
        return 'Get general store and support information: contact details, opening '
            .'hours, location, shipping & returns policy, payment methods, size guide, '
            .'and the list of product categories and collections. Use this for any '
            .'support / customer-service question (ordering, delivery, returns, sizing, '
            .'how to reach the store, what categories exist). Returns JSON.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        // Store info is static-ish — cache the built JSON per locale for 15 minutes.
        return Cache::remember('storeinfo:'.app()->getLocale(), 900, fn () => $this->build());
    }

    private function build(): string
    {
        $freeThreshold = (int) Setting::get('free_shipping_threshold_baisa', 100000);
        $flatRate = (int) Setting::get('shipping_flat_baisa', 2000);

        $categories = Category::query()->active()->roots()->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(fn (Category $category) => [
                'name' => $category->name,
                'url' => route('categories.show', $category->slug),
                'subcategories' => $category->children->pluck('name')->all(),
            ])->all();

        $collections = Collection::query()->active()->orderBy('sort_order')
            ->get(['name', 'slug', 'year'])
            ->map(fn (Collection $collection) => [
                'name' => $collection->name,
                'year' => $collection->year,
                'url' => route('lookbooks.show', $collection->slug),
            ])->all();

        return json_encode([
            'store_name' => config('app.name'),
            'about' => 'Omani fashion house founded in 2006 by designer Amal Al Raisi, '
                .'creating contemporary ready-to-wear rooted in Omani heritage. In 2026 the '
                .'house marks twenty years with the "Echoes of Time" anniversary capsule.',
            'contact' => [
                'phone' => Setting::get('contact_phone', '+968 2400 0000'),
                'email' => Setting::get('contact_email', 'hello@amalalraisi.com'),
                'address' => Setting::get('address_line', 'Al Athaiba, Muscat, Sultanate of Oman'),
                'showroom' => 'Showroom in Al Athaiba, Muscat — visitors welcome through the week.',
                'opening_hours' => 'Thursday–Saturday, 9am–1pm and 4pm–9pm.',
                'contact_page' => route('contact'),
            ],
            'shipping' => [
                'coverage' => 'Delivery across the Sultanate of Oman.',
                'dispatch_time' => 'Orders are dispatched within 2–4 business days.',
                'flat_rate' => format_omr($flatRate),
                'free_shipping' => 'Free delivery on orders above '.format_omr($freeThreshold).'.',
                'page' => route('pages.shipping'),
            ],
            'returns' => [
                'policy' => 'Unworn items in their original condition may be returned or '
                    .'exchanged within 14 days of delivery. Made-to-order and sale pieces are '
                    .'final sale. To arrange a return, contact us with your order number.',
                'page' => route('pages.shipping'),
            ],
            'payment' => [
                'methods' => 'Card payment processed securely by Thawani at checkout. '
                    .'Card details are never stored on our servers. Prices are in Omani Rial (OMR), '
                    .'inclusive of applicable taxes.',
            ],
            'size_guide' => [
                'note' => 'Measurements in centimetres (bust / waist / hips).',
                'sizes' => [
                    'S' => 'Bust 82–86, Waist 64–68, Hips 90–94',
                    'M' => 'Bust 87–91, Waist 69–73, Hips 95–99',
                    'L' => 'Bust 92–97, Waist 74–79, Hips 100–105',
                    'XL' => 'Bust 98–104, Waist 80–86, Hips 106–112',
                ],
                'page' => route('size-guide'),
            ],
            'categories' => $categories,
            'collections' => $collections,
            'useful_links' => [
                'shop' => route('shop.index'),
                'occasions' => route('occasions.index'),
                'lookbooks' => route('lookbooks.index'),
                'search' => route('search'),
                'about' => route('about'),
                'contact' => route('contact'),
            ],
        ], JSON_UNESCAPED_UNICODE);
    }
}
