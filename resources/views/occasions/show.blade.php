<x-layouts.storefront :title="$tag->name" :description="$tag->description ?: __('Shop the Amal Al Raisi edit for :name.', ['name' => $tag->name])" :image="$tag->coverImageUrl()">
    <section class="relative overflow-hidden">
        <img src="{{ $tag->coverImageUrl() }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-ink/55"></div>
        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 py-28 text-center text-white">
            <p class="text-xs uppercase tracking-[0.25em] text-white/80">{{ __('Occasion') }}</p>
            <h1 class="mt-3 text-4xl lg:text-6xl">{{ $tag->name }}</h1>
            @if ($tag->description)
                <p class="mx-auto mt-4 max-w-2xl text-white/80">{{ $tag->description }}</p>
            @endif
        </div>
    </section>

    <nav class="mx-auto max-w-7xl px-4 sm:px-6 pt-6 text-xs uppercase tracking-[0.18em] text-stone-500">
        <a href="{{ route('occasions.index') }}" class="hover:text-accent">{{ __('Occasions') }}</a>
        <span class="mx-2">/</span>
        <span class="text-ink">{{ $tag->name }}</span>
    </nav>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-10">
        <x-storefront.product-grid :products="$products" :facets="$facets" />
    </section>
</x-layouts.storefront>
