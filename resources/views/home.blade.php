<x-layouts.storefront>
    <section class="bg-sand">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-24 lg:py-36 text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-accent">Omani Fashion House · Since 2006</p>
            <h1 class="mt-5 text-5xl lg:text-7xl text-ink leading-[1.05]">Echoes of Time</h1>
            <p class="mx-auto mt-6 max-w-xl text-base text-stone-600 leading-relaxed">
                A twenty-year celebration of Omani heritage, reimagined for the modern wardrobe.
            </p>
            <div class="mt-10">
                <a href="{{ route('collections.index') }}"
                    class="inline-block bg-ink px-9 py-3.5 text-xs uppercase tracking-[0.2em] text-white hover:bg-accent transition-colors">
                    Shop Collections
                </a>
            </div>
        </div>
    </section>

    @if ($featuredCollections->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-20">
            <h2 class="text-center text-3xl text-ink">Featured Collections</h2>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ($featuredCollections as $collection)
                    <a href="{{ route('collections.show', $collection) }}"
                        class="group relative block aspect-[3/4] overflow-hidden bg-gradient-to-b from-sand to-stone-soft">
                        <div class="flex h-full w-full flex-col items-center justify-center text-center">
                            <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $collection->season ?? $collection->type->getLabel() }}</p>
                            <h3 class="mt-2 font-serif text-3xl text-ink">{{ $collection->name }}</h3>
                        </div>
                        <div class="absolute inset-0 flex items-end justify-center pb-10 opacity-0 transition group-hover:opacity-100">
                            <span class="bg-ink px-6 py-2 text-xs uppercase tracking-[0.2em] text-white">Explore</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-12">
            <h2 class="mb-12 text-center text-3xl text-ink">Signature Pieces</h2>
            <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($newArrivals->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 py-12">
            <div class="mb-12 flex items-center justify-between">
                <h2 class="text-3xl text-ink">New Arrivals</h2>
                <a href="{{ route('search') }}" class="text-xs uppercase tracking-[0.18em] text-accent hover:underline">View all</a>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.storefront>
