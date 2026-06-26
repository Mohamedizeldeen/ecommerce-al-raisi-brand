<x-layouts.storefront title="{{ __('Checkout') }}" :noindex="true">
    <section class="mx-auto max-w-6xl px-4 sm:px-6 py-12">
        <h1 class="mb-8 text-4xl text-ink">{{ __('Checkout') }}</h1>

        @if ($errors->any())
            <div class="mb-6 border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ __('Please check the highlighted fields below.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-10 lg:grid-cols-3">
            @csrf
            <div class="space-y-6 lg:col-span-2">
                <div>
                    <h2 class="mb-4 text-lg text-ink">{{ __('Contact') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @php($u = auth()->user())
                        <x-storefront.field name="customer_name" label="{{ __('Full name') }}" :value="old('customer_name', $defaultAddress?->name ?? $u?->name)" />
                        <x-storefront.field name="customer_email" label="{{ __('Email') }}" type="email" :value="old('customer_email', $u?->email)" />
                        <x-storefront.field name="customer_phone" label="{{ __('Phone') }}" :value="old('customer_phone', $defaultAddress?->phone ?? $u?->phone)" />
                    </div>
                </div>

                <div>
                    <h2 class="mb-4 text-lg text-ink">{{ __('Shipping Address') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><x-storefront.field name="shipping_address_line1" label="{{ __('Address line 1') }}" :value="old('shipping_address_line1', $defaultAddress?->line1)" /></div>
                        <div class="sm:col-span-2"><x-storefront.field name="shipping_address_line2" label="{{ __('Address line 2 (optional)') }}" :value="old('shipping_address_line2', $defaultAddress?->line2)" :required="false" /></div>
                        <x-storefront.field name="shipping_city" label="{{ __('City') }}" :value="old('shipping_city', $defaultAddress?->city)" />
                        <x-storefront.field name="shipping_region" label="{{ __('Region (optional)') }}" :value="old('shipping_region', $defaultAddress?->region)" :required="false" />
                    </div>
                    <div class="mt-4">
                        <label class="mb-1 block text-xs uppercase tracking-[0.15em] text-ink">{{ __('Order notes (optional)') }}</label>
                        <textarea name="notes" rows="3" class="w-full border border-stone-soft px-3 py-2 text-sm focus:border-accent focus:outline-none">{{ old('notes') }}</textarea>
                    </div>
                    <p class="mt-4 text-xs text-stone-500">{{ __('Shipping country: Oman. We deliver within the Sultanate of Oman.') }}</p>
                    @auth
                        <label class="mt-4 flex items-center gap-2 text-sm text-stone-600">
                            <input type="checkbox" name="save_address" value="1" class="border-stone-soft">
                            {{ __('Save this address to my address book') }}
                        </label>
                    @endauth
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-sand p-6">
                    <h2 class="mb-4 text-lg text-ink">{{ __('Your Order') }}</h2>
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
                    <button type="submit"
                        class="mt-6 block w-full bg-ink py-3.5 text-center text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                        {{ __('Place Order') }}
                    </button>
                    <p class="mt-3 text-center text-xs text-stone-500">{{ __('You will be redirected to Thawani to pay securely.') }}</p>
                </div>
            </div>
        </form>
    </section>
</x-layouts.storefront>
