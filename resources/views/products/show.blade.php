@php
    $gallery = $product->displayGalleryUrls();
    $sizes = $product->variants->pluck('size')->filter()->unique()->values();
    $colors = $product->variants->filter(fn ($v) => $v->color)
        ->map(fn ($v) => ['name' => $v->color, 'hex' => $v->color_hex])
        ->unique('name')->values();
    // Per-colour image, in priority order: (1) the variant's own uploaded photo
    // (admin sets it on the variant), (2) a gallery photo tagged with that colour
    // (media custom property 'color'), (3) a rotating gallery image so the swatch
    // always changes the view.
    $variantImageByColor = $product->variants
        ->filter(fn ($v) => $v->color && $v->image_path)
        ->groupBy('color')
        ->map(fn ($group) => $group->first()->imageUrl());
    $galleryMedia = $product->getMedia('gallery');
    $colorImages = [];
    foreach ($colors as $i => $c) {
        $tagged = $galleryMedia->first(fn ($m) => strcasecmp((string) $m->getCustomProperty('color'), (string) $c['name']) === 0);
        $colorImages[$c['name']] = $variantImageByColor[$c['name']]
            ?? $tagged?->getUrl()
            ?? ($gallery[$i % max(count($gallery), 1)] ?? null);
    }
    $variantData = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'size' => $v->size,
        'color' => $v->color,
        'price' => money((int) $v->price_baisa),
        'stock' => (int) $v->stock_qty,
    ])->values();
    $metaDescription = $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150);
@endphp

<x-layouts.storefront :title="$product->meta_title ?: $product->name" :description="$metaDescription" :image="$product->displayImageUrl()">
    @push('head')
        @php
            $productLd = [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'image' => array_values(array_filter($gallery)),
                'description' => strip_tags((string) $product->description),
                'offers' => [
                    '@type' => 'Offer',
                    'price' => (int) $product->base_price_baisa / 1000,
                    'priceCurrency' => 'OMR',
                    'url' => url()->current(),
                    'availability' => $product->in_stock
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/OutOfStock',
                ],
            ];
            if ($reviewStats['count'] > 0) {
                $productLd['aggregateRating'] = [
                    '@type' => 'AggregateRating',
                    'ratingValue' => $reviewStats['avg'],
                    'reviewCount' => $reviewStats['count'],
                ];
                $productLd['review'] = $reviews->take(5)->map(fn ($r) => [
                    '@type' => 'Review',
                    'author' => ['@type' => 'Person', 'name' => $r->author_name],
                    'datePublished' => $r->created_at->toDateString(),
                    'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $r->rating, 'bestRating' => 5],
                    'reviewBody' => $r->body,
                ])->values()->all();
            }
            $breadcrumbLd = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => config('app.name'),
                        'item' => url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => $product->name,
                        'item' => url()->current(),
                    ],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($productLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        <script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush

    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12" x-data="productPage(@js($variantData), @js($colorImages), @js($gallery))">
        <div class="grid gap-10 lg:grid-cols-2">
            {{-- Gallery (main image follows the selected colour) --}}
            <div class="space-y-4 lg:sticky lg:top-28 lg:self-start">
                <div class="aspect-[4/5] overflow-hidden bg-sand">
                    <img src="{{ $gallery[0] ?? '' }}" :src="mainImage" alt="{{ $product->name }}"
                        class="h-full w-full object-cover transition-opacity duration-300">
                </div>
                @if (count($gallery) > 1)
                    <div class="flex gap-3">
                        @foreach ($gallery as $img)
                            <button type="button" @click="mainImage = @js($img)"
                                class="aspect-square w-20 overflow-hidden bg-sand ring-1"
                                :class="mainImage === @js($img) ? 'ring-ink' : 'ring-transparent'">
                                <img src="{{ $img }}" class="h-full w-full object-cover" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div>
                @if ($product->collections->isNotEmpty())
                    <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $product->collections->first()->name }}</p>
                @endif
                <h1 class="mt-2 text-4xl text-ink">{{ $product->name }}</h1>
                @if ($reviewStats['count'] > 0)
                    @php($avgRounded = (int) round($reviewStats['avg']))
                    <a href="#reviews" class="mt-2 inline-flex items-center gap-2 text-sm hover:underline">
                        <span class="text-accent" aria-hidden="true">{{ str_repeat('★', $avgRounded).str_repeat('☆', 5 - $avgRounded) }}</span>
                        <span class="text-stone-500">{{ $reviewStats['avg'] }} · {{ $reviewStats['count'] }} {{ $reviewStats['count'] === 1 ? __('review') : __('reviews') }}</span>
                    </a>
                @endif
                <p class="mt-4 text-xl text-stone-600">
                    <span x-text="price">{{ money((int) $product->base_price_baisa) }}</span>
                    @if ($product->onSale())
                        <span class="ms-2 text-base text-stone-400 line-through">{{ money((int) $product->compare_at_price_baisa) }}</span>
                    @endif
                </p>

                @if ($product->description)
                    <div class="mt-6 leading-relaxed text-stone-600">{!! nl2br(e($product->description)) !!}</div>
                @endif

                {{-- Colours --}}
                @if ($colors->isNotEmpty())
                    <div class="mt-8">
                        <p class="text-xs uppercase tracking-[0.18em] text-ink">{{ __('Colour:') }}
                            <span class="text-stone-500" x-text="color || 'Select'"></span></p>
                        <div class="mt-3 flex flex-wrap gap-3" role="group" aria-label="{{ __('Colour') }}">
                            @foreach ($colors as $c)
                                <button type="button" title="{{ $c['name'] }}" aria-label="{{ $c['name'] }}"
                                    :aria-pressed="color === @js($c['name'])"
                                    @click="color = @js($c['name'])"
                                    class="h-9 w-9 rounded-full border transition"
                                    :class="color === @js($c['name']) ? 'ring-2 ring-ink ring-offset-2' : 'border-stone-soft'"
                                    style="background-color: {{ $c['hex'] ?: '#dddddd' }}"></button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Sizes --}}
                @if ($sizes->isNotEmpty())
                    <div class="mt-6">
                        <div class="flex items-center justify-between">
                            <p class="text-xs uppercase tracking-[0.18em] text-ink">{{ __('Size') }}</p>
                            <a href="/size-guide" class="text-xs text-accent hover:underline">{{ __('Size guide') }}</a>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($sizes as $s)
                                <button type="button" @click="size = @js($s)"
                                    :aria-pressed="size === @js($s)"
                                    class="min-w-12 border px-4 py-2 text-sm transition"
                                    :class="size === @js($s) ? 'border-ink bg-ink text-white' : 'border-stone-soft hover:border-ink'">{{ $s }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Stock + add to cart --}}
                <form method="POST" action="/cart/add" class="mt-8"
                    @submit="if (! current || current.stock < 1) $event.preventDefault()">
                    @csrf
                    <input type="hidden" name="variant_id" :value="current?.id">
                    <input type="hidden" name="quantity" value="1">

                    <p class="mb-3 text-sm" x-cloak role="status" aria-live="polite">
                        <span x-show="current && current.stock > 3" class="text-green-700">{{ __('In stock') }}</span>
                        <span x-show="current && current.stock > 0 && current.stock <= 3" class="text-accent">{{ __('Low stock — only') }} <span x-text="current?.stock"></span> {{ __('left') }}</span>
                        <span x-show="current && current.stock < 1" class="text-red-600">{{ __('Sold out') }}</span>
                        <span x-show="! current" class="text-stone-400">{{ __('Please select your options') }}</span>
                    </p>

                    <button type="submit"
                        @click.prevent="$store.cart.add(current?.id, 1)"
                        class="w-full bg-ink py-4 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="! current || current.stock < 1 || $store.cart.loading">
                        <span x-show="! $store.cart.loading">{{ __('Add to Cart') }}</span>
                        <span x-show="$store.cart.loading" x-cloak>{{ __('Adding…') }}</span>
                    </button>
                </form>

                {{-- Back-in-stock notify (shows when the chosen variant is sold out) --}}
                <div x-show="current && current.stock < 1" x-cloak class="mt-4">
                    <p class="mb-2 text-sm text-stone-500">{{ __('Out of stock — get notified when it returns.') }}</p>
                    <form method="POST" action="{{ route('stock.notify') }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="variant_id" :value="current?.id">
                        <input type="email" name="email" required placeholder="{{ __('Email address') }}" value="{{ auth()->user()?->email }}"
                            class="w-full min-w-0 border border-stone-soft px-3 py-2 text-sm focus:border-accent focus:outline-none">
                        <button type="submit" class="shrink-0 bg-ink px-4 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">{{ __('Notify me') }}</button>
                    </form>
                </div>

                @auth
                    @php($inWishlist = in_array($product->id, auth()->user()->wishlistProductIds()))
                    <form method="POST" action="{{ route('account.wishlist.toggle', $product) }}" class="mt-3">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 border border-stone-soft py-3.5 text-xs uppercase tracking-[0.2em] text-ink transition hover:border-ink">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                            {{ $inWishlist ? __('In your wishlist') : __('Add to wishlist') }}
                        </button>
                    </form>
                @endauth

                {{-- Virtual try-on (AI: upload a photo, see the product on you) --}}
                @if (config('assistant.try_on.enabled', true))
                    <div x-data="virtualTryOn(@js($product->slug))" class="mt-4">
                        <button type="button" @click="reset(); open = true"
                            class="flex w-full items-center justify-center gap-2 border border-ink py-3.5 text-xs uppercase tracking-[0.2em] text-ink transition hover:bg-ink hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                            </svg>
                            {{ __('Virtual Try-On') }}
                        </button>

                        <div x-show="open" x-cloak x-transition.opacity.duration.300ms @click="open = false"
                            class="fixed inset-0 z-[110] bg-ink/60"></div>
                        <div x-show="open" x-cloak
                            x-transition:enter="transition duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
                            x-transition:enter-start="translate-y-4 opacity-0 sm:scale-95"
                            x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
                            @keydown.escape.window="open = false"
                            x-trap.noscroll="open"
                            role="dialog" aria-modal="true" aria-label="{{ __('Virtual Try-On') }}"
                            class="fixed left-1/2 top-1/2 z-[120] flex max-h-[90vh] w-[calc(100vw-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                            <header class="flex items-center justify-between border-b border-stone-soft px-5 py-4">
                                <div>
                                    <p class="font-serif text-lg text-ink">{{ __('Virtual Try-On') }}</p>
                                    <p class="text-[11px] text-stone-400">{{ $product->name }}</p>
                                </div>
                                <button @click="open = false" aria-label="{{ __('Close') }}" class="text-stone-400 transition hover:text-ink">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                </button>
                            </header>

                            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                                <div x-show="! result">
                                    <p class="mb-4 text-sm text-stone-500">{{ __('Upload a clear, full-length photo of yourself to preview this piece on you.') }}</p>
                                    <label class="flex aspect-[3/4] cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-stone-soft bg-sand/40 transition hover:border-accent">
                                        <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="pick($event)">
                                        <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
                                        <template x-if="! preview"><span class="px-6 text-center text-sm text-stone-400">{{ __('Tap to upload your photo') }}</span></template>
                                    </label>
                                    <p x-show="error" x-text="error" x-cloak class="mt-3 text-sm text-red-600"></p>
                                    <button type="button" @click="run()" :disabled="! file || loading"
                                        class="mt-4 w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent disabled:cursor-not-allowed disabled:opacity-40">
                                        <span x-show="! loading">{{ __('Try it on') }}</span>
                                        <span x-show="loading" x-cloak>{{ __('Creating your look…') }}</span>
                                    </button>
                                </div>

                                <div x-show="result" x-cloak>
                                    <div class="overflow-hidden rounded-xl bg-sand">
                                        <img :src="result" alt="" class="w-full">
                                    </div>
                                    <div class="mt-4 flex gap-2">
                                        <a :href="result" download="amal-try-on.png"
                                            class="flex-1 bg-ink py-3 text-center text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">{{ __('Download') }}</a>
                                        <button type="button" @click="reset()"
                                            class="flex-1 border border-stone-soft py-3 text-xs uppercase tracking-[0.2em] text-ink transition hover:border-ink">{{ __('Try another photo') }}</button>
                                    </div>
                                </div>
                            </div>

                            <p class="border-t border-stone-soft px-5 py-3 text-[11px] leading-relaxed text-stone-400">
                                {{ __('AI-generated preview — results are approximate. Your photo is used only to create this preview and is not stored.') }}
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Specs --}}
                @if ($product->fabric || ! empty($product->specs))
                    <div class="mt-10 border-t border-stone-soft pt-6 text-sm text-stone-600">
                        @if ($product->fabric)
                            <p><span class="uppercase tracking-[0.15em] text-ink">{{ __('Fabric:') }}</span> {{ $product->fabric }}</p>
                        @endif
                        @foreach (($product->specs ?? []) as $key => $value)
                            <p class="mt-1"><span class="uppercase tracking-[0.15em] text-ink">{{ $key }}:</span> {{ $value }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- #11 Style it with — complete the look --}}
        @if ($product->pairings->isNotEmpty())
            <section class="mt-24">
                <h2 class="mb-3 text-center text-3xl text-ink">{{ __('Style it with') }}</h2>
                <p class="mb-12 text-center text-sm text-stone-500">{{ __('Complete the look with these matching pieces.') }}</p>
                <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-4">
                    @foreach ($product->pairings as $pair)
                        <x-storefront.product-card :product="$pair" />
                    @endforeach
                </div>
            </section>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-24">
                <h2 class="mb-12 text-center text-3xl text-ink">{{ __('You may also like') }}</h2>
                <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-4">
                    @foreach ($related as $rel)
                        <x-storefront.product-card :product="$rel" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Customer reviews --}}
        <section class="mt-24 border-t border-stone-soft pt-16" id="reviews">
            <h2 class="mb-2 text-center text-3xl text-ink">{{ __('Customer Reviews') }}</h2>
            @if ($reviewStats['count'] > 0)
                @php($avgRounded = (int) round($reviewStats['avg']))
                <p class="mb-12 text-center text-sm text-stone-500">
                    <span class="text-accent" aria-hidden="true">{{ str_repeat('★', $avgRounded).str_repeat('☆', 5 - $avgRounded) }}</span>
                    {{ $reviewStats['avg'] }} {{ __('out of 5') }} · {{ $reviewStats['count'] }} {{ $reviewStats['count'] === 1 ? __('review') : __('reviews') }}
                </p>
            @else
                <p class="mb-12 text-center text-sm text-stone-500">{{ __('No reviews yet — be the first to review this piece.') }}</p>
            @endif

            <div class="mx-auto grid max-w-5xl gap-12 lg:grid-cols-2">
                {{-- List --}}
                <div class="space-y-6">
                    @foreach ($reviews as $review)
                        <article class="border-b border-stone-soft pb-6">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-medium text-ink">{{ $review->author_name }}</p>
                                <span class="text-sm text-accent" aria-label="{{ $review->rating }} {{ __('out of 5') }}">{{ str_repeat('★', $review->rating).str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            @if ($review->is_verified_purchase)
                                <p class="mt-1 text-[11px] uppercase tracking-[0.15em] text-green-700">{{ __('Verified purchase') }}</p>
                            @endif
                            @if ($review->title)
                                <p class="mt-2 font-medium text-ink">{{ $review->title }}</p>
                            @endif
                            <p class="mt-1 text-sm leading-relaxed text-stone-600">{{ $review->body }}</p>
                            <p class="mt-2 text-xs text-stone-400">{{ $review->created_at->translatedFormat('d M Y') }}</p>
                        </article>
                    @endforeach
                </div>

                {{-- Submit form --}}
                <div>
                    <h3 class="mb-4 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Write a review') }}</h3>
                    <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs text-stone-500">{{ __('Rating') }}</label>
                            <select name="rating" required class="w-full border border-stone-soft bg-white px-3 py-2 text-sm focus:border-accent focus:outline-none">
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} {{ $i === 1 ? __('star') : __('stars') }}</option>
                                @endfor
                            </select>
                        </div>
                        <x-storefront.field name="author_name" label="{{ __('Your name') }}" :value="old('author_name', auth()->user()?->name)" />
                        <x-storefront.field name="author_email" label="{{ __('Email') }}" type="email" :value="old('author_email', auth()->user()?->email)" />
                        <x-storefront.field name="title" label="{{ __('Title (optional)') }}" :value="old('title')" />
                        <div>
                            <label class="mb-1 block text-xs text-stone-500">{{ __('Your review') }}</label>
                            <textarea name="body" rows="4" required class="w-full border border-stone-soft bg-white px-3 py-2 text-sm focus:border-accent focus:outline-none">{{ old('body') }}</textarea>
                            @error('body')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full bg-ink py-3 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">{{ __('Submit review') }}</button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('productPage', (variants, colorImages, gallery) => ({
                    variants,
                    colorImages,
                    gallery,
                    size: null,
                    color: null,
                    mainImage: gallery[0] ?? '',
                    defaultPrice: @js(money((int) $product->base_price_baisa)),
                    init() {
                        // Selecting a colour swaps the main image to that colour's photo.
                        this.$watch('color', (value) => {
                            if (value && this.colorImages[value]) this.mainImage = this.colorImages[value];
                        });
                    },
                    get needsSize() { return this.variants.some(v => v.size) },
                    get needsColor() { return this.variants.some(v => v.color) },
                    get current() {
                        return this.variants.find(v =>
                            (! this.needsSize || v.size === this.size) &&
                            (! this.needsColor || v.color === this.color)
                        ) || null
                    },
                    get price() { return this.current ? this.current.price : this.defaultPrice },
                }))
            })
        </script>
    @endpush
</x-layouts.storefront>
