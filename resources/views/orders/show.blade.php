<x-layouts.storefront :title="__('Order Confirmation')">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
        <div class="text-center">
            <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ __('Thank you') }}</p>
            <h1 class="mt-3 text-4xl text-ink">{{ __('Order') }} {{ $order->order_number }}</h1>
            <p class="mt-3 text-stone-600">{{ __('A confirmation has been sent to') }} {{ $order->customer_email }}.</p>
            <p class="mt-2 inline-flex items-center gap-2 text-sm">
                <span class="text-stone-500">{{ __('Payment:') }}</span>
                <span class="rounded-full bg-sand px-3 py-1 text-xs uppercase tracking-wide text-ink">{{ $order->payment_status->getLabel() }}</span>
            </p>
        </div>

        <div class="mt-10 border border-stone-soft">
            <ul class="divide-y divide-stone-soft">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 px-5 py-4 text-sm">
                        <div>
                            <p class="text-ink">{{ $item->name }}</p>
                            <p class="text-xs text-stone-500">{{ $item->variant_label }} · {{ __('Qty') }} {{ $item->quantity }}</p>
                        </div>
                        <p class="text-ink">{{ format_omr($item->line_total_baisa) }}</p>
                    </li>
                @endforeach
            </ul>
            <dl class="space-y-2 border-t border-stone-soft px-5 py-4 text-sm">
                <div class="flex justify-between"><dt class="text-stone-500">{{ __('Subtotal') }}</dt><dd>{{ format_omr($order->subtotal_baisa) }}</dd></div>
                @if ($order->discount_baisa > 0)
                    <div class="flex justify-between text-accent"><dt>{{ __('Discount') }}</dt><dd>-{{ format_omr($order->discount_baisa) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt class="text-stone-500">{{ __('Shipping') }}</dt><dd>{{ $order->shipping_baisa > 0 ? format_omr($order->shipping_baisa) : __('Free') }}</dd></div>
                <div class="flex justify-between border-t border-stone-soft pt-2 text-base text-ink"><dt>{{ __('Total') }}</dt><dd>{{ format_omr($order->total_baisa) }}</dd></div>
            </dl>
        </div>

        <div class="mt-8 text-sm text-stone-600">
            <h2 class="mb-2 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Shipping to') }}</h2>
            <p>{{ $order->customer_name }}</p>
            <p>{{ $order->shipping_address_line1 }}</p>
            @if ($order->shipping_address_line2)<p>{{ $order->shipping_address_line2 }}</p>@endif
            <p>{{ collect([$order->shipping_city, $order->shipping_region, $order->shipping_country])->filter()->implode(', ') }}</p>
            <p>{{ $order->customer_phone }}</p>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('collections.index') }}" class="text-xs uppercase tracking-[0.2em] text-accent hover:underline">{{ __('Continue shopping') }}</a>
        </div>
    </section>
</x-layouts.storefront>
