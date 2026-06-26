@props(['product'])

<div class="group relative">
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="relative aspect-[4/5] overflow-hidden bg-sand">
            <img src="{{ $product->hasMedia('gallery') ? ($product->getFirstMediaUrl('gallery', 'card') ?: $product->displayImageUrl()) : $product->displayImageUrl() }}"
                alt="{{ $product->name }}" loading="lazy" decoding="async" width="600" height="750"
                class="h-full w-full object-cover transition-transform duration-[1200ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.06]">

            @if (! $product->in_stock)
                <span class="absolute start-3 top-3 z-10 bg-white/90 px-2 py-1 text-[10px] uppercase tracking-[0.18em] text-ink">{{ __('Sold Out') }}</span>
            @elseif ($product->onSale())
                <span class="absolute start-3 top-3 z-10 bg-accent px-2 py-1 text-[10px] uppercase tracking-[0.18em] text-white">{{ __('Sale') }}</span>
            @endif

            <div class="absolute inset-x-0 bottom-0 translate-y-full transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:translate-y-0">
                <span class="block bg-ink/95 py-3 text-center text-[11px] uppercase tracking-[0.2em] text-white backdrop-blur">{{ __('View Product') }}</span>
            </div>
        </div>

        <div class="mt-3 text-center">
            <h3 class="text-xs uppercase tracking-[0.15em] text-ink transition-colors group-hover:text-accent">{{ $product->name }}</h3>
            <p class="mt-1 text-sm">
                @if ($product->onSale())
                    <span class="text-accent">{{ money((int) $product->base_price_baisa) }}</span>
                    <span class="ms-1 text-stone-400 line-through">{{ money((int) $product->compare_at_price_baisa) }}</span>
                @else
                    <span class="text-stone-500">{{ money((int) $product->base_price_baisa) }}</span>
                @endif
            </p>
        </div>
    </a>

    @auth
        @php($inWishlist = in_array($product->id, auth()->user()->wishlistProductIds()))
        <form method="POST" action="{{ route('account.wishlist.toggle', $product) }}" class="absolute end-2 top-2 z-20">
            @csrf
            <button type="submit" aria-label="{{ $inWishlist ? __('Remove from wishlist') : __('Add to wishlist') }}"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-ink shadow-sm transition hover:text-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </button>
        </form>
    @endauth
</div>
