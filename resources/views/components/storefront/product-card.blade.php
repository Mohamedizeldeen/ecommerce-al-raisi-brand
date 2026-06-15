@props(['product'])

<a href="{{ route('products.show', $product) }}" class="group block">
    <div class="relative aspect-[4/5] overflow-hidden bg-sand">
        <img src="{{ $product->displayImageUrl() }}" alt="{{ $product->name }}" loading="lazy"
            class="h-full w-full object-cover transition-transform duration-[1200ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.06]">

        @unless ($product->in_stock)
            <span class="absolute left-3 top-3 z-10 bg-white/90 px-2 py-1 text-[10px] uppercase tracking-[0.18em] text-ink">Sold Out</span>
        @endunless

        <div class="absolute inset-x-0 bottom-0 translate-y-full transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:translate-y-0">
            <span class="block bg-ink/95 py-3 text-center text-[11px] uppercase tracking-[0.2em] text-white backdrop-blur">View Product</span>
        </div>
    </div>

    <div class="mt-3 text-center">
        <h3 class="text-xs uppercase tracking-[0.15em] text-ink transition-colors group-hover:text-accent">{{ $product->name }}</h3>
        <p class="mt-1 text-sm text-stone-500">{{ $product->formatted_price }}</p>
    </div>
</a>
