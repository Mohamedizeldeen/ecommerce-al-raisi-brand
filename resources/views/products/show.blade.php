@php
    $gallery = $product->displayGalleryUrls();
    $sizes = $product->variants->pluck('size')->filter()->unique()->values();
    $colors = $product->variants->filter(fn ($v) => $v->color)
        ->map(fn ($v) => ['name' => $v->color, 'hex' => $v->color_hex])
        ->unique('name')->values();
    $variantData = $product->variants->map(fn ($v) => [
        'id' => $v->id,
        'size' => $v->size,
        'color' => $v->color,
        'price' => format_omr($v->price_baisa),
        'stock' => (int) $v->stock_qty,
    ])->values();
    $metaDescription = $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150);
@endphp

<x-layouts.storefront :title="$product->name" :description="$metaDescription">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12" x-data="productPage(@js($variantData))">
        <div class="grid gap-10 lg:grid-cols-2">
            {{-- Gallery --}}
            <div class="space-y-4 lg:sticky lg:top-28 lg:self-start" x-data="{ active: 0 }">
                <div class="aspect-[4/5] overflow-hidden bg-sand">
                    @foreach ($gallery as $i => $img)
                        <img src="{{ $img }}" alt="{{ $product->name }}" x-show="active === {{ $i }}"
                            class="h-full w-full object-cover">
                    @endforeach
                </div>
                @if (count($gallery) > 1)
                    <div class="flex gap-3">
                        @foreach ($gallery as $i => $img)
                            <button type="button" @click="active = {{ $i }}"
                                class="aspect-square w-20 overflow-hidden bg-sand ring-1"
                                :class="active === {{ $i }} ? 'ring-ink' : 'ring-transparent'">
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
                <p class="mt-4 text-xl text-stone-600" x-text="price">{{ $product->formatted_price }}</p>

                @if ($product->description)
                    <div class="mt-6 leading-relaxed text-stone-600">{!! nl2br(e($product->description)) !!}</div>
                @endif

                {{-- Colours --}}
                @if ($colors->isNotEmpty())
                    <div class="mt-8">
                        <p class="text-xs uppercase tracking-[0.18em] text-ink">Colour:
                            <span class="text-stone-500" x-text="color || 'Select'"></span></p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($colors as $c)
                                <button type="button" title="{{ $c['name'] }}"
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
                            <p class="text-xs uppercase tracking-[0.18em] text-ink">Size</p>
                            <a href="/size-guide" class="text-xs text-accent hover:underline">Size guide</a>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($sizes as $s)
                                <button type="button" @click="size = @js($s)"
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

                    <p class="mb-3 text-sm" x-cloak>
                        <span x-show="current && current.stock > 3" class="text-green-700">In stock</span>
                        <span x-show="current && current.stock > 0 && current.stock <= 3" class="text-accent">Low stock — only <span x-text="current?.stock"></span> left</span>
                        <span x-show="current && current.stock < 1" class="text-red-600">Sold out</span>
                        <span x-show="! current" class="text-stone-400">Please select your options</span>
                    </p>

                    <button type="submit"
                        @click.prevent="$store.cart.add(current?.id, 1)"
                        class="w-full bg-ink py-4 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="! current || current.stock < 1 || $store.cart.loading">
                        <span x-show="! $store.cart.loading">Add to Cart</span>
                        <span x-show="$store.cart.loading" x-cloak>Adding…</span>
                    </button>
                </form>

                {{-- Specs --}}
                @if ($product->fabric || ! empty($product->specs))
                    <div class="mt-10 border-t border-stone-soft pt-6 text-sm text-stone-600">
                        @if ($product->fabric)
                            <p><span class="uppercase tracking-[0.15em] text-ink">Fabric:</span> {{ $product->fabric }}</p>
                        @endif
                        @foreach (($product->specs ?? []) as $key => $value)
                            <p class="mt-1"><span class="uppercase tracking-[0.15em] text-ink">{{ $key }}:</span> {{ $value }}</p>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-24">
                <h2 class="mb-12 text-center text-3xl text-ink">You may also like</h2>
                <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-4">
                    @foreach ($related as $rel)
                        <x-storefront.product-card :product="$rel" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('productPage', (variants) => ({
                    variants,
                    size: null,
                    color: null,
                    defaultPrice: @js($product->formatted_price),
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
