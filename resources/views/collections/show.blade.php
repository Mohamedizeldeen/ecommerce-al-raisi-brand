<x-layouts.storefront :title="$collection->name" :description="$collection->description" :image="$collection->coverImageUrl()">
    <section class="relative overflow-hidden">
        <img src="{{ $collection->coverImageUrl() }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-ink/55"></div>
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 py-28 text-center text-white">
            <p class="text-xs uppercase tracking-[0.25em] text-white/80">{{ $collection->season ?? $collection->type->getLabel() }}</p>
            <h1 class="mt-3 text-4xl lg:text-6xl">{{ $collection->name }}</h1>
            @if ($collection->description)
                <p class="mx-auto mt-4 max-w-2xl text-white/80">{{ $collection->description }}</p>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-14">
        <x-storefront.product-grid :products="$products" :facets="$facets" />
    </section>
</x-layouts.storefront>
