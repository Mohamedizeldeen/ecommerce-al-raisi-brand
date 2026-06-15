@php
    $nav = [
        ['label' => 'Collections', 'url' => '/collections'],
        ['label' => 'Ready-to-Wear', 'url' => '/category/ready-to-wear'],
        ['label' => 'Accessories', 'url' => '/category/accessories'],
        ['label' => 'Lifestyle', 'url' => '/category/lifestyle'],
        ['label' => 'About', 'url' => '/about'],
        ['label' => 'Contact', 'url' => '/contact'],
    ];
@endphp

<header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 24"
    class="sticky top-0 z-50 transition-all duration-500"
    :class="scrolled ? 'bg-white/85 shadow-[0_1px_0_rgba(22,19,15,0.08)] backdrop-blur-md' : 'bg-white'">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="flex h-20 items-center justify-between gap-4">
            <button @click="open = ! open" class="-ml-2 p-2 text-ink lg:hidden" aria-label="Toggle menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>

            <a href="/" class="font-serif text-2xl uppercase tracking-[0.15em] text-ink sm:text-3xl">
                {{ config('app.name') }}
            </a>

            <nav class="hidden items-center gap-9 text-[11px] uppercase tracking-[0.22em] text-ink/80 lg:flex">
                @foreach ($nav as $item)
                    <a href="{{ $item['url'] }}" class="link-underline hover:text-accent">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-4 text-ink sm:gap-5">
                <a href="/search" aria-label="Search" class="transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" aria-label="Account" class="transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>
                <a href="/cart" @click.prevent="$store.cart.openDrawer()" aria-label="Cart" class="relative transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                    <span x-show="$store.cart.count > 0" x-cloak x-text="$store.cart.count"
                        class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-ink px-1 text-[10px] font-medium text-white"></span>
                </a>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak x-transition.origin.top.duration.300ms class="border-t border-stone-soft bg-white lg:hidden">
        <nav class="flex flex-col px-4 py-3 text-sm uppercase tracking-[0.15em] text-ink/80">
            @foreach ($nav as $item)
                <a href="{{ $item['url'] }}" class="py-2 hover:text-accent">{{ $item['label'] }}</a>
            @endforeach
        </nav>
    </div>
</header>
