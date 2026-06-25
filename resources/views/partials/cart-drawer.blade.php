<div class="flex h-full flex-col">
    @if ($items->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center gap-4 px-6 text-center">
            <p class="text-stone-500">{{ __('Your bag is empty.') }}</p>
            <a href="{{ route('collections.index') }}" class="text-xs uppercase tracking-[0.2em] text-accent link-underline">{{ __('Continue shopping') }}</a>
        </div>
    @else
        <ul class="flex-1 divide-y divide-stone-soft overflow-y-auto px-6">
            @foreach ($items as $item)
                <li class="flex gap-4 py-5">
                    <a href="{{ route('products.show', $item->variant->product) }}" class="h-24 w-20 flex-shrink-0 overflow-hidden bg-sand">
                        <img src="{{ $item->variant->product->thumbImageUrl() }}" alt="" class="h-full w-full object-cover">
                    </a>
                    <div class="flex flex-1 flex-col">
                        <div class="flex justify-between gap-2 text-sm">
                            <span class="uppercase tracking-wide text-ink">{{ $item->variant->product->name }}</span>
                            <span>{{ format_omr($item->lineTotalBaisa()) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-stone-500">{{ $item->variant->label }} &middot; {{ __('Qty') }} {{ $item->quantity }}</p>
                        <form method="POST" action="{{ route('cart.remove', $item) }}" class="mt-auto">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-[11px] uppercase tracking-[0.15em] text-stone-400 transition hover:text-red-600">{{ __('Remove') }}</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="border-t border-stone-soft px-6 py-5">
            <div class="flex justify-between text-sm">
                <span class="text-stone-500">{{ __('Subtotal') }}</span>
                <span class="text-ink">{{ format_omr($summary['subtotal']) }}</span>
            </div>
            <p class="mt-1 text-xs text-stone-400">{{ __('Shipping & taxes calculated at checkout.') }}</p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('cart.index') }}" class="border border-ink py-3 text-center text-xs uppercase tracking-[0.2em] text-ink transition hover:bg-sand">{{ __('View Bag') }}</a>
                <a href="{{ route('checkout.index') }}" class="bg-ink py-3 text-center text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">{{ __('Checkout') }}</a>
            </div>
        </div>
    @endif
</div>
