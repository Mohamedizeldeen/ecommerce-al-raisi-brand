<x-layouts.storefront :title="__('Search')">
    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-12">
        <form method="GET" action="{{ route('search') }}" class="mx-auto max-w-xl">
            <div class="flex items-center border-b border-ink">
                <input type="search" name="q" value="{{ $term }}" placeholder="{{ __('Search products…') }}" autofocus aria-label="{{ __('Search') }}"
                    class="w-full bg-transparent py-3 text-lg placeholder-stone-400 focus:outline-none">
                <button type="submit" class="px-2 text-ink hover:text-accent" aria-label="{{ __('Search') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>
        </form>

        <div class="mt-12">
            @if ($term !== '')
                <p class="mb-8 text-sm text-stone-500">{{ __('Results for') }} &ldquo;<span class="text-ink">{{ $term }}</span>&rdquo;</p>
            @endif
            <x-storefront.product-grid :products="$products" />
        </div>
    </section>
</x-layouts.storefront>
