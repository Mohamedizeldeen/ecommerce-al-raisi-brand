@props(['product'])

<a href="{{ route('products.show', $product) }}" class="group block">
    <div class="relative aspect-[4/5] overflow-hidden bg-sand">
        @if ($url = $product->primaryImageUrl())
            <img src="{{ $url }}" alt="{{ $product->name }}"
                class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center px-6 text-center">
                <span class="font-serif text-xl leading-snug text-accent/70">{{ $product->name }}</span>
            </div>
        @endif

        @unless ($product->in_stock)
            <span class="absolute left-3 top-3 bg-white/90 px-2 py-1 text-[10px] uppercase tracking-[0.18em] text-ink">
                Sold Out
            </span>
        @endunless
    </div>

    <div class="mt-3 text-center">
        <h3 class="text-xs uppercase tracking-[0.15em] text-ink group-hover:text-accent transition-colors">{{ $product->name }}</h3>
        <p class="mt-1 text-sm text-stone-500">{{ $product->formatted_price }}</p>
    </div>
</a>
