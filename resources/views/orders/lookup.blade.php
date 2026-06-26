<x-layouts.storefront title="{{ __('Track Your Order') }}" :noindex="true">
    <section class="mx-auto max-w-md px-4 sm:px-6 py-16">
        <h1 class="mb-3 text-center text-3xl text-ink">{{ __('Track your order') }}</h1>
        <p class="mb-8 text-center text-sm text-stone-500">{{ __('Enter your order number and the email used at checkout.') }}</p>

        <form method="POST" action="{{ route('orders.lookup.submit') }}" class="space-y-5">
            @csrf
            <x-storefront.field name="order_number" label="{{ __('Order number') }}" :value="old('order_number')" />
            <x-storefront.field name="email" label="{{ __('Email') }}" type="email" :value="old('email')" />

            <button type="submit" class="w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                {{ __('Find my order') }}
            </button>
        </form>
    </section>
</x-layouts.storefront>
