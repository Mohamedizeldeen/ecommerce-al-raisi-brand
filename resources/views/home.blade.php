<x-layouts.storefront>
    {{-- Hero --}}
    <section class="relative flex min-h-[88vh] items-center justify-center overflow-hidden bg-sand">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-white/30 via-transparent to-sand"></div>
        <div class="pointer-events-none absolute -right-32 top-10 h-[28rem] w-[28rem] rounded-full bg-accent/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -left-24 bottom-0 h-80 w-80 rounded-full bg-stone-soft/50 blur-3xl"></div>

        <div class="relative z-10 mx-auto max-w-3xl px-4 text-center">
            <p class="animate-fade-up text-xs uppercase tracking-[0.4em] text-accent" style="animation-delay:.1s">Omani Fashion House · Since 2006</p>
            <h1 class="animate-fade-up mt-6 text-6xl leading-[0.92] text-ink sm:text-7xl lg:text-[7.5rem]" style="animation-delay:.25s">
                Echoes<br><span class="italic text-accent-dark">of Time</span>
            </h1>
            <p class="animate-fade-up mx-auto mt-8 max-w-xl text-base leading-relaxed text-stone-600" style="animation-delay:.4s">
                A twenty-year celebration of Omani heritage, reimagined for the modern wardrobe.
            </p>
            <div class="animate-fade-up mt-10" style="animation-delay:.55s">
                <a href="{{ route('collections.index') }}"
                    class="group inline-flex items-center gap-3 bg-ink px-9 py-4 text-xs uppercase tracking-[0.25em] text-white transition hover:bg-accent">
                    Shop Collections
                    <span class="transition-transform duration-300 group-hover:translate-x-1">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="animate-scroll-cue absolute bottom-8 left-1/2 -translate-x-1/2 text-ink/50">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
    </section>

    {{-- Featured collections --}}
    @if ($featuredCollections->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-24">
            <div x-data class="reveal mb-14 text-center" x-intersect.once="$el.classList.add('is-visible')">
                <p class="text-xs uppercase tracking-[0.3em] text-accent">Curated</p>
                <h2 class="mt-3 text-4xl text-ink">Featured Collections</h2>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($featuredCollections as $collection)
                    <a href="{{ route('collections.show', $collection) }}" x-data
                        class="reveal group relative block aspect-[3/4] overflow-hidden bg-gradient-to-b from-sand to-stone-soft"
                        x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ $loop->index * 120 }}ms">
                        <div class="relative flex h-full w-full flex-col items-center justify-center text-center transition-transform duration-[1200ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                            <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $collection->season ?? $collection->type->getLabel() }}</p>
                            <h3 class="mt-2 font-serif text-3xl text-ink">{{ $collection->name }}</h3>
                        </div>
                        <div class="absolute inset-x-0 bottom-0 flex justify-center pb-8 opacity-0 transition duration-500 group-hover:opacity-100">
                            <span class="bg-ink px-6 py-2 text-[11px] uppercase tracking-[0.2em] text-white">Explore</span>
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
                    <p class="text-xs uppercase tracking-[0.3em] text-accent">Iconic</p>
                    <h2 class="mt-3 text-4xl text-ink">Signature Pieces</h2>
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
                    <p class="text-xs uppercase tracking-[0.3em] text-accent">Just In</p>
                    <h2 class="mt-3 text-4xl text-ink">New Arrivals</h2>
                </div>
                <a href="{{ route('search') }}" class="text-xs uppercase tracking-[0.18em] text-accent link-underline">View all</a>
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
