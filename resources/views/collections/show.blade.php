<x-layouts.storefront :title="$collection->name" :description="$collection->description">
    <section class="bg-sand">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16 text-center">
            <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $collection->season ?? $collection->type->getLabel() }}</p>
            <h1 class="mt-3 text-4xl text-ink lg:text-5xl">{{ $collection->name }}</h1>
            @if ($collection->description)
                <p class="mx-auto mt-4 max-w-2xl text-stone-600">{{ $collection->description }}</p>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-14">
        <x-storefront.product-grid :products="$products" />
    </section>
</x-layouts.storefront>
