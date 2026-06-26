<x-layouts.storefront title="{{ __('Shopping Bag') }}" :noindex="true">
    <section class="mx-auto max-w-5xl px-4 sm:px-6 py-12">
        <h1 class="mb-8 text-4xl text-ink">{{ __('Shopping Bag') }}</h1>

        @if ($items->isEmpty())
            <div class="py-16 text-center">
                <p class="text-stone-500">{{ __('Your bag is empty.') }}</p>
                <a href="{{ route('collections.index') }}" class="mt-4 inline-block text-xs uppercase tracking-[0.2em] text-accent hover:underline">{{ __('Continue shopping') }}</a>
            </div>
        @else
            <div class="grid gap-10 lg:grid-cols-3">
                <div class="divide-y divide-stone-soft lg:col-span-2">
                    @foreach ($items as $item)
                        <div class="flex gap-4 py-6">
                            <a href="{{ route('products.show', $item->variant->product) }}" class="h-28 w-24 flex-shrink-0 overflow-hidden bg-sand">
                                <img src="{{ $item->variant->product->thumbImageUrl() }}" alt="" class="h-full w-full object-cover">
                            </a>
                            <div class="flex-1">
                                <div class="flex justify-between gap-4">
                                    <div>
                                        <h3 class="text-sm uppercase tracking-wide text-ink">{{ $item->variant->product->name }}</h3>
                                        <p class="mt-1 text-xs text-stone-500">{{ $item->variant->label }}</p>
                                    </div>
                                    <p class="text-sm text-ink">{{ format_omr($item->lineTotalBaisa()) }}</p>
                                </div>
                                <div class="mt-4 flex items-center justify-between">
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                        @csrf @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->variant->stock_qty }}"
                                            class="w-16 border border-stone-soft px-2 py-1 text-sm focus:border-accent focus:outline-none">
                                        <button type="submit" class="text-xs uppercase tracking-[0.12em] text-accent hover:underline">{{ __('Update') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.remove', $item) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs uppercase tracking-[0.12em] text-stone-400 hover:text-red-600">{{ __('Remove') }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-sand p-6">
                        <h2 class="mb-4 text-lg text-ink">{{ __('Order Summary') }}</h2>

                        <form method="POST" action="{{ route('cart.coupon') }}" class="mb-5 flex gap-2">
                            @csrf
                            <input type="text" name="code" value="{{ $cart->coupon_code }}" placeholder="{{ __('Promo code') }}"
                                class="w-full border border-stone-soft bg-white px-3 py-2 text-sm focus:border-accent focus:outline-none">
                            <button type="submit" class="border border-ink px-3 text-xs uppercase tracking-[0.12em] hover:bg-ink hover:text-white transition">{{ __('Apply') }}</button>
                        </form>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-stone-500">{{ __('Subtotal') }}</dt><dd>{{ format_omr($summary['subtotal']) }}</dd></div>
                            @if ($summary['discount'] > 0)
                                <div class="flex justify-between text-accent"><dt>{{ __('Discount') }}</dt><dd>-{{ format_omr($summary['discount']) }}</dd></div>
                            @endif
                            <div class="flex justify-between"><dt class="text-stone-500">{{ __('Shipping') }}</dt><dd>{{ $summary['shipping'] > 0 ? format_omr($summary['shipping']) : __('Free') }}</dd></div>
                            <div class="flex justify-between border-t border-stone-soft pt-3 text-base text-ink"><dt>{{ __('Total') }}</dt><dd>{{ format_omr($summary['total']) }}</dd></div>
                            @if ($summary['tax'] > 0)
                                <div class="flex justify-between text-xs text-stone-400"><dt>{{ __('Includes VAT (:percent%)', ['percent' => $summary['vat_percent']]) }}</dt><dd>{{ format_omr($summary['tax']) }}</dd></div>
                            @endif
                        </dl>

                        <a href="{{ route('checkout.index') }}"
                            class="mt-6 block bg-ink py-3.5 text-center text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                            {{ __('Proceed to Checkout') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>
</x-layouts.storefront>
