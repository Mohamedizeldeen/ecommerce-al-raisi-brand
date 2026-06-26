<x-layouts.storefront :title="__('My Orders')" :noindex="true">
    <section class="mx-auto max-w-5xl px-4 sm:px-6 py-12">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl text-ink">{{ __('My Orders') }}</h1>
            <a href="{{ route('account.dashboard') }}" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline">{{ __('Account') }}</a>
        </div>

        @if ($orders->count())
            <div class="divide-y divide-stone-soft border-y border-stone-soft">
                @foreach ($orders as $order)
                    <a href="{{ route('account.orders.show', $order) }}"
                        class="grid grid-cols-4 items-center gap-4 py-4 text-sm transition hover:text-accent">
                        <span class="font-medium">{{ $order->order_number }}</span>
                        <span class="text-stone-500">{{ $order->created_at->format('d M Y') }}</span>
                        <span>{{ format_omr($order->total_baisa) }}</span>
                        <span class="justify-self-end rounded-full bg-sand px-3 py-1 text-xs uppercase tracking-wide text-ink">{{ $order->payment_status->getLabel() }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $orders->links() }}</div>
        @else
            <p class="py-6 text-stone-500">{{ __('You have no orders yet.') }}</p>
        @endif
    </section>
</x-layouts.storefront>
