<x-layouts.storefront :title="__('My Wishlist')" :noindex="true">
    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-12">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl text-ink">{{ __('My Wishlist') }}</h1>
            <a href="{{ route('account.dashboard') }}" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline">{{ __('My Account') }}</a>
        </div>

        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        @else
            <p class="py-20 text-center text-stone-500">{{ __('Your wishlist is empty.') }}</p>
        @endif
    </section>
</x-layouts.storefront>
