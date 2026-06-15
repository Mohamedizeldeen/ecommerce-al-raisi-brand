<x-layouts.storefront :title="$category->name">
    <section class="border-b border-stone-soft">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 text-center">
            <h1 class="text-4xl text-ink">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mx-auto mt-3 max-w-2xl text-stone-600">{{ $category->description }}</p>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-14">
        <x-storefront.product-grid :products="$products" />
    </section>
</x-layouts.storefront>
