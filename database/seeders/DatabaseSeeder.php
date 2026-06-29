<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Enums\TagGroup;
use App\Models\BlogCategory;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Showcase;
use App\Models\Tag;
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
        ])->forceFill(['is_admin' => true, 'role' => 'admin'])->save();

        User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        // --- Categories (evergreen SDM garment taxonomy) ---------------------
        // Each launch category's slug matches its ProductType so its landing page
        // auto-includes every product of that type. Accessories sits alongside
        // with attribute sub-categories (Scarves, Bags) as their own landings.
        $cat = [];
        foreach (ProductType::garmentCases() as $i => $type) {
            $cat[$type->value] = Category::create([
                'name' => $this->bilingual($type->getLabel()),
                'slug' => $type->slug(),
                'sort_order' => $i + 1,
            ]);
        }

        // Attribute sub-landing under a backbone family (SDM subcategory template,
        // e.g. "Embroidered Dresses" under Evening Dresses) — curated via the pivot.
        $embroidered = Category::create([
            'name' => ['en' => 'Embroidered Dresses', 'ar' => 'الفساتين المطرّزة'],
            'slug' => 'embroidered-dresses',
            'parent_id' => $cat[ProductType::EveningDress->value]->id,
            'sort_order' => 1,
        ]);

        $accessories = Category::create(['name' => $this->bilingual('Accessories'), 'slug' => 'accessories', 'sort_order' => 20]);
        $scarves = Category::create(['name' => $this->bilingual('Scarves'), 'slug' => 'scarves', 'parent_id' => $accessories->id, 'sort_order' => 1]);
        $bags = Category::create(['name' => $this->bilingual('Bags'), 'slug' => 'bags', 'parent_id' => $accessories->id, 'sort_order' => 2]);

        // --- Tags (occasion layer + demoted seasons) -------------------------
        $weddingGuest = Tag::create(['name' => $this->bilingual('Wedding Guest'), 'slug' => 'wedding-guest', 'group' => TagGroup::Occasion, 'sort_order' => 1]);
        $eid = Tag::create(['name' => $this->bilingual('Eid & Ramadan'), 'slug' => 'eid-ramadan', 'group' => TagGroup::Occasion, 'sort_order' => 2]);
        $resort = Tag::create(['name' => $this->bilingual('Resort'), 'slug' => 'resort', 'group' => TagGroup::Occasion, 'sort_order' => 3]);
        Tag::create(['name' => $this->bilingual('Spring/Summer 25'), 'slug' => 'ss25', 'group' => TagGroup::Season, 'sort_order' => 1]);
        Tag::create(['name' => $this->bilingual('Autumn/Winter 24'), 'slug' => 'aw24', 'group' => TagGroup::Season, 'sort_order' => 2]);

        // --- Collections -----------------------------------------------------
        $echoes = Collection::create(['name' => 'Echoes of Time', 'slug' => 'echoes-of-time', 'type' => 'capsule', 'year' => 2026, 'is_featured' => true, 'sort_order' => 0, 'description' => 'A twenty-year anniversary capsule celebrating two decades of Omani craft.']);
        $voyage = Collection::create(['name' => 'Summer Voyage', 'slug' => 'summer-voyage', 'type' => 'capsule', 'year' => 2026, 'is_featured' => true, 'sort_order' => 1, 'description' => 'High summer and festive capsule for the season of travel.']);
        $ss25 = Collection::create(['name' => 'Spring/Summer 25', 'slug' => 'ss25', 'season' => 'SS25', 'type' => 'seasonal', 'year' => 2025, 'is_featured' => true, 'sort_order' => 2]);
        $aw24 = Collection::create(['name' => 'Autumn/Winter 24', 'slug' => 'aw24', 'season' => 'AW24', 'type' => 'seasonal', 'year' => 2024, 'sort_order' => 3]);
        $ss24 = Collection::create(['name' => 'Spring/Summer 24', 'slug' => 'ss24', 'season' => 'SS24', 'type' => 'seasonal', 'year' => 2024, 'sort_order' => 4]);
        $aw23 = Collection::create(['name' => 'Autumn/Winter 23', 'slug' => 'aw23', 'season' => 'AW23', 'type' => 'seasonal', 'year' => 2023, 'sort_order' => 5]);

        // --- Products (per evergreen category, with occasion tags) -----------
        $sizes = ['S', 'M', 'L', 'XL'];
        $this->makeProducts(6, $cat[ProductType::Kaftan->value], ProductType::Kaftan, [$echoes, $ss25], $sizes, [$eid, $weddingGuest]);
        $this->makeProducts(6, $cat[ProductType::EveningDress->value], ProductType::EveningDress, [$ss25, $voyage], $sizes, [$weddingGuest, $eid]);
        $this->makeProducts(6, $cat[ProductType::MaxiDress->value], ProductType::MaxiDress, [$voyage, $ss24], $sizes, [$resort, $weddingGuest]);
        $this->makeProducts(4, $cat[ProductType::Jumpsuit->value], ProductType::Jumpsuit, [$ss25], $sizes, [$resort]);
        $this->makeProducts(4, $cat[ProductType::SetCoord->value], ProductType::SetCoord, [$voyage], $sizes, [$resort]);
        $this->makeProducts(4, $cat[ProductType::Abaya->value], ProductType::Abaya, [$aw24, $aw23], $sizes, [$eid]);
        $this->makeProducts(3, $cat[ProductType::Jalabiya->value], ProductType::Jalabiya, [$echoes], $sizes, [$eid]);
        $this->makeProducts(4, $cat[ProductType::ModestDress->value], ProductType::ModestDress, [$ss24], $sizes, [$weddingGuest]);
        $this->makeProducts(6, $scarves, ProductType::Scarf, [$ss25, $voyage], [null], colorsPerProduct: 3);
        $this->makeProducts(4, $bags, ProductType::Bag, [$aw24], [null], colorsPerProduct: 1, singleVariant: true);

        // Curate a few evening dresses into the "Embroidered Dresses" sub-landing.
        Product::ofType(ProductType::EveningDress)->take(2)->get()
            ->each(fn (Product $product) => $product->categories()->attach($embroidered->id));

        // --- Style-it-with pairings (complete the look) ----------------------
        // Pair each garment with a few accessories (scarves, bags).
        $accessoryIds = Product::whereHas('categories', fn ($q) => $q->whereIn('slug', ['scarves', 'bags']))->pluck('id')->all();
        Product::whereHas('categories', fn ($q) => $q->whereNotIn('slug', ['scarves', 'bags', 'accessories']))->get()
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

        // --- Blog (categories + articles that link to products) --------------
        $styling = BlogCategory::create(['name' => ['en' => 'Styling Guides', 'ar' => 'أدلة التنسيق'], 'slug' => 'styling-guides', 'sort_order' => 1]);
        $heritage = BlogCategory::create(['name' => ['en' => 'Cultural Heritage', 'ar' => 'التراث الثقافي'], 'slug' => 'cultural-heritage', 'sort_order' => 2]);

        $kaftanIds = Product::ofType(ProductType::Kaftan)->pluck('id');
        $eveningIds = Product::ofType(ProductType::EveningDress)->pluck('id');

        $articles = [
            [
                'category' => $styling, 'products' => $kaftanIds->take(3),
                'title' => ['en' => 'How to Style a Kaftan for Eid', 'ar' => 'كيف تنسّقين القفطان في العيد'],
                'slug' => 'styling-a-kaftan-for-eid',
                'excerpt' => ['en' => 'Three effortless ways to wear our kaftans this Eid.', 'ar' => 'ثلاث طرق أنيقة لارتداء قفاطيننا هذا العيد.'],
            ],
            [
                'category' => $styling, 'products' => $eveningIds->take(3),
                'title' => ['en' => 'Dressing for a Wedding as a Guest', 'ar' => 'إطلالة ضيفة العرس'],
                'slug' => 'wedding-guest-dressing',
                'excerpt' => ['en' => 'Occasion dresses that strike the right note.', 'ar' => 'فساتين مناسبات تمنحكِ الإطلالة المثالية.'],
            ],
            [
                'category' => $heritage, 'products' => collect(),
                'title' => ['en' => 'The Omani Roots of Our Prints', 'ar' => 'الجذور العُمانية لنقوشنا'],
                'slug' => 'omani-roots-of-our-prints',
                'excerpt' => ['en' => 'The heritage motifs behind the house.', 'ar' => 'الزخارف التراثية التي تلهم الدار.'],
            ],
        ];

        foreach ($articles as $i => $article) {
            $post = Post::create([
                'type' => Post::TYPE_BLOG,
                'blog_category_id' => $article['category']->id,
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'],
                'body' => ['en' => '<p>'.$article['excerpt']['en'].'</p>', 'ar' => '<p>'.$article['excerpt']['ar'].'</p>'],
                'published_at' => now()->subDays($i + 1),
                'sort_order' => $i,
                'is_active' => true,
            ]);

            if ($article['products']->isNotEmpty()) {
                $post->products()->attach(
                    $article['products']->values()->mapWithKeys(fn ($id, $j) => [$id => ['sort_order' => $j]])->all()
                );
            }
        }
    }

    /**
     * Build a translatable {en, ar} value, pulling the Arabic from lang/ar.json
     * when present (falls back to the English string).
     *
     * @return array{en: string, ar: string}
     */
    private function bilingual(string $en): array
    {
        $ar = trans($en, [], 'ar');

        return ['en' => $en, 'ar' => is_string($ar) ? $ar : $en];
    }

    /**
     * Create products of a given type in a category, link them to collections
     * and a random subset of occasion tags, and build size x color variants.
     *
     * @param  array<int, Collection>  $collections
     * @param  array<int, string|null>  $sizes
     * @param  array<int, Tag>  $occasionTags
     */
    private function makeProducts(int $count, Category $category, ProductType $type, array $collections, array $sizes, array $occasionTags = [], int $colorsPerProduct = 2, bool $singleVariant = false): void
    {
        Product::factory()->count($count)->create(['product_type' => $type])->each(function (Product $product) use ($category, $collections, $sizes, $occasionTags, $colorsPerProduct, $singleVariant) {
            $product->categories()->attach($category->id);

            $pick = collect($collections)->random(rand(1, count($collections)));
            $product->collections()->attach($pick->pluck('id')->all());

            if (! empty($occasionTags)) {
                $tagPick = collect($occasionTags)->shuffle()->take(rand(1, count($occasionTags)));
                $product->tags()->attach($tagPick->pluck('id')->all());
            }

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
