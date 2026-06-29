<x-layouts.storefront :overHero="true" :description="__('Amal Al Raisi — an Omani fashion house since 2006. Discover handcrafted abayas, kaftans and occasion wear, with delivery across Oman.')">
    {{-- Hero — full-bleed, sits under the transparent nav (pulled up by the header height) --}}
    <section class="relative -mt-20 flex h-[100svh] min-h-[34rem] items-end overflow-hidden">
        {{-- Hero video background (muted autoplay loop), shown on ALL screens.
             The hero image is the poster so it shows instantly and stays as the
             fallback where autoplay is blocked or the browser can't play video. --}}
        <video class="absolute inset-0 h-full w-full object-cover animate-fade-in"
            autoplay muted loop playsinline preload="auto"
            poster="{{ asset_version('images/heroes/hero.jpg') }}">
            <source src="{{ asset_version('videos/hero.mp4') }}" type="video/mp4">
            <img src="{{ asset_version('images/heroes/hero.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        </video>
        {{-- Legibility wash, weighted to the bottom so the imagery leads --}}
        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-ink/75 via-ink/25 to-transparent"></div>

        <div class="relative z-10 w-full px-6 pb-16 sm:px-10 sm:pb-24">
            <div class="max-w-xl text-white">
                <p class="animate-fade-up text-[11px] uppercase tracking-[0.4em] text-white/80" style="animation-delay:.1s">{{ __('Omani Fashion House · Since 2006') }}</p>
                <h1 class="animate-fade-up mt-5 font-serif text-5xl leading-[0.95] sm:text-7xl" style="animation-delay:.25s">
                    {{ __('Echoes') }} <span class="italic text-white/90">{{ __('of Time') }}</span>
                </h1>
                <a href="{{ route('shop.index') }}"
                    class="animate-fade-up group mt-8 inline-flex items-center gap-3 border-b border-white/50 pb-1.5 text-xs uppercase tracking-[0.25em] text-white transition hover:border-accent hover:text-accent" style="animation-delay:.4s">
                    {{ __('Discover the Collection') }}
                    <span class="transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1"><span class="inline-block rtl:-scale-x-100" aria-hidden="true">&rarr;</span></span>
                </a>
            </div>
        </div>
    </section>

    {{-- Shop by category (evergreen product categories — SDM backbone) --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-24">
            <div x-data class="reveal mb-12 flex items-end justify-between" x-intersect.once="$el.classList.add('is-visible')">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Shop') }}</p>
                    <h2 class="mt-3 text-4xl text-ink">{{ __('Shop by Category') }}</h2>
                </div>
                <a href="{{ route('shop.index') }}" class="text-xs uppercase tracking-[0.18em] text-accent link-underline">{{ __('View all') }}</a>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" x-data
                        class="reveal group relative block aspect-[3/2] overflow-hidden bg-stone-soft"
                        x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                        <img src="{{ $category->coverImageUrl($loop->index) }}" alt="{{ $category->name }}" loading="lazy"
                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                        <div class="absolute inset-0 bg-ink/40 transition duration-500 group-hover:bg-ink/55"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-center text-white">
                            <h3 class="font-serif text-3xl">{{ $category->name }}</h3>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Shop by occasion (tag-driven occasion layer) --}}
    @if ($occasions->isNotEmpty())
        <section class="bg-sand py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 text-center">
                <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Occasions') }}</p>
                <h2 class="mt-3 text-4xl text-ink">{{ __('Shop by Occasion') }}</h2>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    @foreach ($occasions as $occasion)
                        <a href="{{ route('occasions.show', $occasion->slug) }}"
                            class="border border-stone-soft bg-white/60 px-6 py-3 text-xs uppercase tracking-[0.18em] text-ink/80 transition hover:border-accent hover:text-accent">
                            {{ $occasion->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured collections --}}
    @if ($featuredCollections->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-24">
            <div x-data class="reveal mb-14 text-center" x-intersect.once="$el.classList.add('is-visible')">
                <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Curated') }}</p>
                <h2 class="mt-3 text-4xl text-ink">{{ __('Featured Collections') }}</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($featuredCollections as $collection)
                    <a href="{{ route('lookbooks.show', $collection) }}" x-data
                        class="reveal group relative block aspect-[3/4] overflow-hidden bg-stone-soft"
                        x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ $loop->index * 120 }}ms">
                        <img src="{{ $collection->coverImageUrl($loop->index) }}" alt="{{ $collection->name }}" loading="lazy"
                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1200ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 text-center text-white">
                            <p class="text-xs uppercase tracking-[0.25em] text-white/80">{{ $collection->season ?? __($collection->type->getLabel()) }}</p>
                            <h3 class="mt-2 font-serif text-3xl">{{ $collection->name }}</h3>
                            <span class="mt-3 inline-block text-[11px] uppercase tracking-[0.2em] opacity-0 transition duration-500 group-hover:opacity-100">{{ __('Explore') }} <span class="inline-block rtl:-scale-x-100" aria-hidden="true">&rarr;</span></span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Signature pieces --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="bg-sand py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6">
                <div x-data class="reveal mb-14 text-center" x-intersect.once="$el.classList.add('is-visible')">
                    <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Iconic') }}</p>
                    <h2 class="mt-3 text-4xl text-ink">{{ __('Signature Pieces') }}</h2>
                </div>
                <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($featuredProducts as $product)
                        <div x-data class="reveal" x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ ($loop->index % 4) * 90 }}ms">
                            <x-storefront.product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- New arrivals --}}
    @if ($newArrivals->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-24">
            <div x-data class="reveal mb-12 flex items-end justify-between" x-intersect.once="$el.classList.add('is-visible')">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Just In') }}</p>
                    <h2 class="mt-3 text-4xl text-ink">{{ __('New Arrivals') }}</h2>
                </div>
                <a href="{{ route('search') }}" class="text-xs uppercase tracking-[0.18em] text-accent link-underline">{{ __('View all') }}</a>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <div x-data class="reveal" x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ ($loop->index % 4) * 90 }}ms">
                        <x-storefront.product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.storefront>
