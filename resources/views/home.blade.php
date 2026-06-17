<x-layouts.storefront>
    {{-- Hero --}}
    <section class="relative flex min-h-[90vh] items-center justify-center overflow-hidden">
        {{-- Hero video background (muted autoplay loop), shown on ALL screens.
             The vertical 720x1280 clip suits phones especially. The hero image is the
             poster so it shows instantly and stays as the fallback where autoplay is
             blocked (e.g. iOS Low Power Mode) or the browser can't play video. --}}
        <video class="absolute inset-0 h-full w-full object-cover animate-fade-in"
            autoplay muted loop playsinline preload="auto"
            poster="{{ asset_version('images/heroes/hero.jpg') }}">
            <source src="{{ asset_version('videos/hero.mp4') }}" type="video/mp4">
            <img src="{{ asset_version('images/heroes/hero.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        </video>
        <div class="absolute inset-0 bg-gradient-to-b from-ink/70 via-ink/45 to-ink/75"></div>

        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center text-white">
            <p class="animate-fade-up text-xs uppercase tracking-[0.4em] text-white/80" style="animation-delay:.1s">{{ __('Omani Fashion House · Since 2006') }}</p>
            <h1 class="animate-fade-up mt-6 text-6xl leading-[0.92] sm:text-7xl lg:text-[7.5rem]" style="animation-delay:.25s">
                {{ __('Echoes') }}<br><span class="italic text-white/85">{{ __('of Time') }}</span>
            </h1>
            <p class="animate-fade-up mx-auto mt-8 max-w-xl text-base leading-relaxed text-white/80" style="animation-delay:.4s">
                {{ __('A twenty-year celebration of Omani heritage, reimagined for the modern wardrobe.') }}
            </p>
            <div class="animate-fade-up mt-10" style="animation-delay:.55s">
                <a href="{{ route('collections.index') }}"
                    class="group inline-flex items-center gap-3 bg-white px-9 py-4 text-xs uppercase tracking-[0.25em] text-ink transition hover:bg-accent hover:text-white">
                    {{ __('Shop Collections') }}
                    <span class="transition-transform duration-300 group-hover:translate-x-1">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="animate-scroll-cue absolute bottom-8 left-1/2 -translate-x-1/2 text-white/60">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
    </section>

    {{-- Featured collections --}}
    @if ($featuredCollections->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-24">
            <div x-data class="reveal mb-14 text-center" x-intersect.once="$el.classList.add('is-visible')">
                <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Curated') }}</p>
                <h2 class="mt-3 text-4xl text-ink">{{ __('Featured Collections') }}</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($featuredCollections as $collection)
                    <a href="{{ route('collections.show', $collection) }}" x-data
                        class="reveal group relative block aspect-[3/4] overflow-hidden bg-stone-soft"
                        x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ $loop->index * 120 }}ms">
                        <img src="{{ $collection->coverImageUrl($loop->index) }}" alt="{{ $collection->name }}"
                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1200ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/80 via-ink/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 text-center text-white">
                            <p class="text-xs uppercase tracking-[0.25em] text-white/80">{{ $collection->season ?? __($collection->type->getLabel()) }}</p>
                            <h3 class="mt-2 font-serif text-3xl">{{ $collection->name }}</h3>
                            <span class="mt-3 inline-block text-[11px] uppercase tracking-[0.2em] opacity-0 transition duration-500 group-hover:opacity-100">{{ __('Explore') }} &rarr;</span>
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
