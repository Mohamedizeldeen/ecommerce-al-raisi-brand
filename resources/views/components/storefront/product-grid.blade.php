@props(['products', 'facets' => ['sizes' => [], 'colors' => []]])

@php
    $facets = $facets ?: ['sizes' => [], 'colors' => []];
    $hasActiveFilters = request()->hasAny(['size', 'color', 'min_price', 'max_price', 'in_stock']);
    $clearKeep = array_filter(['q' => request('q'), 'sort' => request('sort')]);
@endphp

<div>
    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 border-b border-stone-soft pb-6 text-sm">
        {{-- Preserve search term, sort, etc. while the fields below override the facets. --}}
        @foreach (request()->except(['size', 'color', 'min_price', 'max_price', 'in_stock', 'page']) as $key => $value)
            @if (! is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        @if (! empty($facets['sizes']))
            <label class="flex flex-col gap-1">
                <span class="text-[10px] uppercase tracking-[0.15em] text-stone-500">{{ __('Size') }}</span>
                <select name="size" class="border border-stone-soft bg-white px-3 py-2 focus:border-accent focus:outline-none">
                    <option value="">{{ __('All sizes') }}</option>
                    @foreach ($facets['sizes'] as $s)
                        <option value="{{ $s }}" @selected(request('size') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        @if (! empty($facets['colors']))
            <label class="flex flex-col gap-1">
                <span class="text-[10px] uppercase tracking-[0.15em] text-stone-500">{{ __('Colour') }}</span>
                <select name="color" class="border border-stone-soft bg-white px-3 py-2 focus:border-accent focus:outline-none">
                    <option value="">{{ __('All colours') }}</option>
                    @foreach ($facets['colors'] as $c)
                        <option value="{{ $c }}" @selected(request('color') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label class="flex flex-col gap-1">
            <span class="text-[10px] uppercase tracking-[0.15em] text-stone-500">{{ __('Min price') }}</span>
            <input type="number" name="min_price" min="0" step="0.001" value="{{ request('min_price') }}"
                class="w-24 border border-stone-soft bg-white px-3 py-2 focus:border-accent focus:outline-none">
        </label>

        <label class="flex flex-col gap-1">
            <span class="text-[10px] uppercase tracking-[0.15em] text-stone-500">{{ __('Max price') }}</span>
            <input type="number" name="max_price" min="0" step="0.001" value="{{ request('max_price') }}"
                class="w-24 border border-stone-soft bg-white px-3 py-2 focus:border-accent focus:outline-none">
        </label>

        <label class="flex items-center gap-2 py-2">
            <input type="checkbox" name="in_stock" value="1" @checked(request('in_stock')) class="border-stone-soft">
            <span class="text-stone-600">{{ __('In stock only') }}</span>
        </label>

        <button type="submit" class="bg-ink px-4 py-2 text-xs uppercase tracking-[0.18em] text-white transition hover:bg-accent">{{ __('Filter') }}</button>

        @if ($hasActiveFilters)
            <a href="{{ url()->current().($clearKeep ? '?'.http_build_query($clearKeep) : '') }}"
                class="px-2 py-2 text-xs uppercase tracking-[0.12em] text-stone-500 transition hover:text-ink">{{ __('Clear') }}</a>
        @endif
    </form>

    {{-- Count + sort --}}
    <div class="mb-8 flex items-center justify-between border-b border-stone-soft pb-4">
        <p class="text-xs uppercase tracking-[0.18em] text-stone-500">{{ $products->total() }} {{ $products->total() === 1 ? __('item') : __('items') }}</p>

        <form method="GET" class="text-sm">
            @foreach (request()->except(['sort', 'page']) as $key => $value)
                @if (! is_array($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <select name="sort" onchange="this.form.submit()" aria-label="{{ __('Sort products') }}"
                class="border border-stone-soft bg-white px-3 py-2 text-xs uppercase tracking-[0.12em] focus:border-accent focus:outline-none">
                <option value="newest" @selected(! request('sort') || request('sort') === 'newest')>{{ __('Newest') }}</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: Low to High') }}</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: High to Low') }}</option>
            </select>
        </form>
    </div>

    @if ($products->count())
        <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($products as $product)
                <div x-data class="reveal" x-intersect.once="$el.classList.add('is-visible')" style="transition-delay: {{ ($loop->index % 4) * 90 }}ms">
                    <x-storefront.product-card :product="$product" />
                </div>
            @endforeach
        </div>

        <div class="mt-14">
            {{ $products->links() }}
        </div>
    @else
        <p class="py-20 text-center text-stone-500">{{ __('No products found.') }}</p>
    @endif
</div>
