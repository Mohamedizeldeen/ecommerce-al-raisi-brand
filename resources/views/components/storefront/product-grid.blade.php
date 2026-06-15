@props(['products'])

<div>
    <div class="mb-8 flex items-center justify-between border-b border-stone-soft pb-4">
        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">{{ $products->total() }} item{{ $products->total() === 1 ? '' : 's' }}</p>

        <form method="GET" class="text-sm">
            @foreach (request()->except(['sort', 'page']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <select name="sort" onchange="this.form.submit()"
                class="border border-stone-soft bg-white px-3 py-2 text-xs uppercase tracking-[0.12em] focus:border-accent focus:outline-none">
                <option value="newest" @selected(! request('sort') || request('sort') === 'newest')>Newest</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
            </select>
        </form>
    </div>

    @if ($products->count())
        <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($products as $product)
                <x-storefront.product-card :product="$product" />
            @endforeach
        </div>

        <div class="mt-14">
            {{ $products->links() }}
        </div>
    @else
        <p class="py-20 text-center text-stone-500">No products found.</p>
    @endif
</div>
