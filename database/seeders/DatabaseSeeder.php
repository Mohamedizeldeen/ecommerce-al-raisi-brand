<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Showcase;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Color palette used to build product variants. */
    private array $palette = [
        ['name' => 'Vanilla', 'hex' => '#efe7d6'],
        ['name' => 'Blossom Pink', 'hex' => '#e7b7c2'],
        ['name' => 'Dusty Teal', 'hex' => '#5f8b87'],
        ['name' => 'Desert Sand', 'hex' => '#d8c3a5'],
        ['name' => 'Midnight', 'hex' => '#1f2330'],
        ['name' => 'Primrose', 'hex' => '#f3d27a'],
        ['name' => 'Ivory', 'hex' => '#f7f3ea'],
        ['name' => 'Bronze', 'hex' => '#8a6d4b'],
    ];

    public function run(): void
    {
        // --- Users -----------------------------------------------------------
        User::factory()->create([
            'name' => 'Amal Admin',
            'email' => 'admin@amalalraisi.com',
        ])->forceFill(['is_admin' => true])->save();

        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        // --- Categories ------------------------------------------------------
        $readyToWear = Category::create(['name' => 'Ready-to-Wear', 'slug' => 'ready-to-wear', 'sort_order' => 1]);
        $accessories = Category::create(['name' => 'Accessories', 'slug' => 'accessories', 'sort_order' => 2]);
        $soft = Category::create(['name' => 'Soft', 'slug' => 'accessories-soft', 'parent_id' => $accessories->id, 'sort_order' => 1]);
        $leather = Category::create(['name' => 'Leather', 'slug' => 'accessories-leather', 'parent_id' => $accessories->id, 'sort_order' => 2]);
        $lifestyle = Category::create(['name' => 'Lifestyle', 'slug' => 'lifestyle', 'sort_order' => 3]);

        // --- Collections -----------------------------------------------------
        $echoes = Collection::create(['name' => 'Echoes of Time', 'slug' => 'echoes-of-time', 'type' => 'capsule', 'year' => 2026, 'is_featured' => true, 'sort_order' => 0, 'description' => 'A twenty-year anniversary capsule celebrating two decades of Omani craft.']);
        $voyage = Collection::create(['name' => 'Summer Voyage', 'slug' => 'summer-voyage', 'type' => 'capsule', 'year' => 2026, 'is_featured' => true, 'sort_order' => 1, 'description' => 'High summer and festive capsule for the season of travel.']);
        $ss25 = Collection::create(['name' => 'Spring/Summer 25', 'slug' => 'ss25', 'season' => 'SS25', 'type' => 'seasonal', 'year' => 2025, 'is_featured' => true, 'sort_order' => 2]);
        $aw24 = Collection::create(['name' => 'Autumn/Winter 24', 'slug' => 'aw24', 'season' => 'AW24', 'type' => 'seasonal', 'year' => 2024, 'sort_order' => 3]);
        $ss24 = Collection::create(['name' => 'Spring/Summer 24', 'slug' => 'ss24', 'season' => 'SS24', 'type' => 'seasonal', 'year' => 2024, 'sort_order' => 4]);
        $aw23 = Collection::create(['name' => 'Autumn/Winter 23', 'slug' => 'aw23', 'season' => 'AW23', 'type' => 'seasonal', 'year' => 2023, 'sort_order' => 5]);

        // --- Products --------------------------------------------------------
        $this->makeProducts(16, $readyToWear, [$ss25, $aw24, $echoes], ['S', 'M', 'L', 'XL'], colorsPerProduct: 2);
        $this->makeProducts(8, $soft, [$ss25, $voyage], [null], colorsPerProduct: 3);
        $this->makeProducts(5, $leather, [$aw24, $aw23], ['S', 'M', 'L'], colorsPerProduct: 2);
        $this->makeProducts(5, $lifestyle, [$voyage, $ss24], [null], colorsPerProduct: 1, singleVariant: true);

        // --- Style-it-with pairings (complete the look) ----------------------
        $accessoryIds = Product::whereHas('categories', fn ($q) => $q->whereIn('slug', ['accessories-soft', 'accessories-leather', 'lifestyle']))->pluck('id')->all();
        Product::whereHas('categories', fn ($q) => $q->where('slug', 'ready-to-wear'))->get()
            ->each(function (Product $product) use ($accessoryIds) {
                $pick = collect($accessoryIds)->shuffle()->take(3)->values();
                $product->pairings()->sync($pick->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->all());
            });

        // --- Coupon ----------------------------------------------------------
        Coupon::create([
            'code' => 'WELCOME10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        // --- Store settings --------------------------------------------------
        $settings = [
            'shipping_flat_baisa' => '2000',           // OMR 2.000 flat rate
            'free_shipping_threshold_baisa' => '100000', // free over OMR 100.000
            'newsletter_discount_percent' => '10',
            'contact_email' => 'hello@amalalraisi.com',
            'contact_phone' => '+968 2400 0000',
            'address_line' => 'Al Athaiba, Muscat, Sultanate of Oman',
            'instagram_url' => 'https://instagram.com/',
            'facebook_url' => 'https://facebook.com/',
        ];

        foreach ($settings as $key => $value) {
            Setting::put($key, $value);
        }

        // --- The Atelier (behind the scenes / fashion shows) -----------------
        $showcases = [
            ['title' => 'Spring/Summer 2026 Runway', 'subtitle' => 'Muscat · Private Viewing', 'type' => 'fashion_show', 'sort_order' => 0, 'description' => 'The full film from our latest runway — twenty years of the house, reimagined on the catwalk.'],
            ['title' => 'Inside the Atelier', 'subtitle' => 'Hand-finishing & fittings', 'type' => 'behind_the_scenes', 'sort_order' => 1, 'description' => 'A quiet morning at the cutting table, where each piece is shaped, pinned and hand-finished in limited runs.'],
            ['title' => 'The Art of the Print', 'subtitle' => 'From sketch to silk', 'type' => 'design', 'sort_order' => 2, 'description' => 'Following a motif from first sketch to printed silk — the story behind the prints that define the house.'],
        ];

        foreach ($showcases as $showcase) {
            Showcase::create($showcase);
        }
    }

    /**
     * Create products in a category, link them to collections, and build
     * size x color variants from the palette.
     *
     * @param  array<int, Collection>  $collections
     * @param  array<int, string|null>  $sizes
     */
    private function makeProducts(int $count, Category $category, array $collections, array $sizes, int $colorsPerProduct = 2, bool $singleVariant = false): void
    {
        Product::factory()->count($count)->create()->each(function (Product $product) use ($category, $collections, $sizes, $colorsPerProduct, $singleVariant) {
            $product->categories()->attach($category->id);

            $pick = collect($collections)->random(rand(1, count($collections)));
            $product->collections()->attach($pick->pluck('id')->all());

            if ($singleVariant) {
                ProductVariant::factory()->for($product)->create([
                    'size' => '',
                    'color' => '',
                    'color_hex' => null,
                    'sku' => 'AMR-'.$product->id.'-STD',
                    'stock_qty' => rand(3, 20),
                ]);

                return;
            }

            $colors = collect($this->palette)->shuffle()->take($colorsPerProduct);
            $index = 0;

            foreach ($sizes as $size) {
                foreach ($colors as $color) {
                    $index++;
                    ProductVariant::factory()->for($product)->create([
                        'size' => $size ?? '',
                        'color' => $color['name'],
                        'color_hex' => $color['hex'],
                        'sku' => 'AMR-'.$product->id.'-'.$index,
                        'stock_qty' => rand(0, 10),
                    ]);
                }
            }
        });
    }
}
