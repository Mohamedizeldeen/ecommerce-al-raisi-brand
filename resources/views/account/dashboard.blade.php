<x-layouts.storefront :title="__('My Account')">
    <section class="mx-auto max-w-5xl px-4 sm:px-6 py-12">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl text-ink">{{ __('My Account') }}</h1>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline">{{ __('Sign out') }}</button>
            </form>
        </div>

        <p class="text-stone-600">{{ __('Welcome back,') }} {{ $user->name }}.</p>

        <div class="mt-10">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl text-ink">{{ __('Recent Orders') }}</h2>
                <a href="{{ route('account.orders') }}" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline">{{ __('View all') }}</a>
            </div>

            @forelse ($recentOrders as $order)
                <a href="{{ route('account.orders.show', $order) }}"
                    class="flex items-center justify-between gap-4 border-b border-stone-soft py-4 text-sm transition hover:text-accent">
                    <span class="font-medium">{{ $order->order_number }}</span>
                    <span class="text-stone-500">{{ $order->created_at->format('d M Y') }}</span>
                    <span>{{ format_omr($order->total_baisa) }}</span>
                    <span class="rounded-full bg-sand px-3 py-1 text-xs uppercase tracking-wide text-ink">{{ $order->payment_status->getLabel() }}</span>
                </a>
            @empty
                <p class="py-6 text-stone-500">{{ __('You have no orders yet.') }}</p>
            @endforelse
        </div>
    </section>
</x-layouts.storefront>
