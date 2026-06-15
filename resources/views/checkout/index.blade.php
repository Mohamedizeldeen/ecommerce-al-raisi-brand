<x-layouts.storefront title="Checkout">
    <section class="mx-auto max-w-6xl px-4 sm:px-6 py-12">
        <h1 class="mb-8 text-4xl text-ink">Checkout</h1>

        @if ($errors->any())
            <div class="mb-6 border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                Please check the highlighted fields below.
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-10 lg:grid-cols-3">
            @csrf
            <div class="space-y-6 lg:col-span-2">
                <div>
                    <h2 class="mb-4 text-lg text-ink">Contact</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @php($u = auth()->user())
                        <x-storefront.field name="customer_name" label="Full name" :value="old('customer_name', $u?->name)" />
                        <x-storefront.field name="customer_email" label="Email" type="email" :value="old('customer_email', $u?->email)" />
                        <x-storefront.field name="customer_phone" label="Phone" :value="old('customer_phone', $u?->phone)" />
                    </div>
                </div>

                <div>
                    <h2 class="mb-4 text-lg text-ink">Shipping Address</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><x-storefront.field name="shipping_address_line1" label="Address line 1" :value="old('shipping_address_line1')" /></div>
                        <div class="sm:col-span-2"><x-storefront.field name="shipping_address_line2" label="Address line 2 (optional)" :value="old('shipping_address_line2')" :required="false" /></div>
                        <x-storefront.field name="shipping_city" label="City" :value="old('shipping_city')" />
                        <x-storefront.field name="shipping_region" label="Region (optional)" :value="old('shipping_region')" :required="false" />
                    </div>
                    <div class="mt-4">
                        <label class="mb-1 block text-xs uppercase tracking-[0.15em] text-ink">Order notes (optional)</label>
                        <textarea name="notes" rows="3" class="w-full border border-stone-soft px-3 py-2 text-sm focus:border-accent focus:outline-none">{{ old('notes') }}</textarea>
                    </div>
                    <p class="mt-4 text-xs text-stone-500">Shipping country: Oman. We deliver within the Sultanate of Oman.</p>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-sand p-6">
                    <h2 class="mb-4 text-lg text-ink">Your Order</h2>
                    <ul class="mb-4 divide-y divide-stone-soft text-sm">
                        @foreach ($cart->items as $item)
                            <li class="flex justify-between gap-2 py-2">
                                <span class="text-stone-600">{{ $item->variant->product->name }}
                                    <span class="text-stone-400">× {{ $item->quantity }}</span></span>
                                <span>{{ format_omr($item->lineTotalBaisa()) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="space-y-2 border-t border-stone-soft pt-3 text-sm">
                        <div class="flex justify-between"><dt class="text-stone-500">Subtotal</dt><dd>{{ format_omr($summary['subtotal']) }}</dd></div>
                        @if ($summary['discount'] > 0)
                            <div class="flex justify-between text-accent"><dt>Discount</dt><dd>-{{ format_omr($summary['discount']) }}</dd></div>
                        @endif
                        <div class="flex justify-between"><dt class="text-stone-500">Shipping</dt><dd>{{ $summary['shipping'] > 0 ? format_omr($summary['shipping']) : 'Free' }}</dd></div>
                        <div class="flex justify-between border-t border-stone-soft pt-3 text-base text-ink"><dt>Total</dt><dd>{{ format_omr($summary['total']) }}</dd></div>
                    </dl>
                    <button type="submit"
                        class="mt-6 block w-full bg-ink py-3.5 text-center text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                        Place Order
                    </button>
                    <p class="mt-3 text-center text-xs text-stone-500">You will be redirected to Thawani to pay securely.</p>
                </div>
            </div>
        </form>
    </section>
</x-layouts.storefront>
