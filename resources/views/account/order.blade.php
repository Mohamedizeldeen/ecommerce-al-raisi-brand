<x-layouts.storefront :title="__('Order').' '.$order->order_number" :noindex="true">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-12">
        <a href="{{ route('account.orders') }}" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline"><span class="inline-block rtl:-scale-x-100" aria-hidden="true">&larr;</span> {{ __('Back to orders') }}</a>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-3xl text-ink">{{ __('Order') }} {{ $order->order_number }}</h1>
            <div class="flex items-center gap-3">
                <span class="rounded-full bg-sand px-3 py-1 text-xs uppercase tracking-wide text-ink">{{ $order->payment_status->getLabel() }}</span>
                <form method="POST" action="{{ route('account.orders.reorder', $order) }}">
                    @csrf
                    <button type="submit" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline">{{ __('Reorder') }}</button>
                </form>
            </div>
        </div>
        <p class="mt-1 text-sm text-stone-500">{{ __('Placed') }} {{ $order->created_at->translatedFormat('d M Y, H:i') }} · {{ $order->status->getLabel() }}</p>

        @if ($order->shipped_at && $order->tracking_number)
            <div class="mt-4 border border-accent/30 bg-sand/40 px-5 py-4 text-sm">
                <p class="text-xs uppercase tracking-[0.18em] text-accent">{{ __('Tracking') }}</p>
                <p class="mt-1 text-ink">@if ($order->carrier){{ $order->carrier }} — @endif<span class="font-mono">{{ $order->tracking_number }}</span></p>
            </div>
        @endif

        <div class="mt-8 border border-stone-soft">
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
                @if ($order->tax_baisa > 0)
                    <div class="flex justify-between text-xs text-stone-400"><dt>{{ __('Includes VAT (:percent%)', ['percent' => $order->vat_percent]) }}</dt><dd>{{ format_omr($order->tax_baisa) }}</dd></div>
                @endif
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

        @if ($order->notes)
            <div class="mt-8 text-sm text-stone-600">
                <h2 class="mb-2 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Order notes') }}</h2>
                <p>{{ $order->notes }}</p>
            </div>
        @endif

        @php
            // Customer-friendly labels per status value — never expose internal
            // staff notes (e.g. oversell "fulfil manually" lines) to the customer.
            $timelineLabels = [
                'pending' => __('Order placed'),
                'paid' => __('Payment confirmed'),
                'processing' => __('Processing'),
                'shipped' => __('Shipped'),
                'completed' => __('Completed'),
                'cancelled' => __('Cancelled'),
                'failed' => __('Payment failed'),
                'refunded' => __('Refunded'),
            ];
        @endphp
        @if ($order->statusHistories->isNotEmpty())
            <div class="mt-8">
                <h2 class="mb-3 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Order timeline') }}</h2>
                <ol class="space-y-3 border-s border-stone-soft ps-5">
                    @foreach ($order->statusHistories as $history)
                        <li class="relative text-sm">
                            <span class="absolute -start-[1.45rem] top-1.5 h-2 w-2 rounded-full bg-accent" aria-hidden="true"></span>
                            <p class="text-ink">{{ $timelineLabels[$history->to_status] ?? __(ucfirst($history->to_status)) }}</p>
                            <p class="text-xs text-stone-500">{{ $history->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif
    </section>
</x-layouts.storefront>
