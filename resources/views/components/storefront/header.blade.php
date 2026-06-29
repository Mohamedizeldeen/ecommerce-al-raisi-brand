@props(['overHero' => false])
@php
    // Full nav lives inside the Menu overlay — the bar itself stays minimal.
    $nav = [
        ['label' => 'Shop', 'url' => '/shop'],
        ['label' => 'Occasions', 'url' => '/occasions'],
        ['label' => 'Lookbooks', 'url' => '/lookbooks'],
        ['label' => 'The Atelier', 'url' => '/atelier'],
        ['label' => 'Blog', 'url' => '/blog'],
        ['label' => 'Press', 'url' => '/press'],
        ['label' => 'About', 'url' => '/about'],
        ['label' => 'Contact', 'url' => '/contact'],
    ];

    $isActive = fn (string $url) => request()->is(ltrim($url, '/'));
@endphp

<header x-data="{ open: false, scrolled: false, overHero: @js($overHero) }"
    @scroll.window="scrolled = window.scrollY > 24"
    @keydown.escape.window="open = false"
    x-effect="document.body.style.overflow = open ? 'hidden' : ''"
    class="sticky top-0 z-50 transition-colors duration-500"
    :class="(scrolled || open) ? 'bg-white text-ink shadow-[0_1px_0_rgba(22,19,15,0.08)]' : (overHero ? 'bg-transparent text-white' : 'bg-white text-ink')">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <div class="relative flex h-20 items-center justify-between">
            {{-- Menu toggle (the only nav entry point) --}}
            <button @click="open = ! open" :aria-expanded="open" aria-controls="site-menu"
                class="group flex items-center gap-2.5 p-1 text-[11px] uppercase tracking-[0.22em] transition hover:text-accent">
                <span class="flex flex-col gap-[5px]" aria-hidden="true">
                    <span class="block h-px w-6 bg-current transition-all duration-300" :class="open ? 'translate-y-[6px] rotate-45' : ''"></span>
                    <span class="block h-px w-6 bg-current transition-all duration-300" :class="open ? '-translate-y-[6px] -rotate-45' : ''"></span>
                </span>
                <span class="hidden sm:inline" x-text="open ? '{{ __('Close') }}' : '{{ __('Menu') }}'">{{ __('Menu') }}</span>
            </button>

            {{-- Wordmark (centered) --}}
            <a href="/" aria-label="{{ config('app.name') }}" class="absolute left-1/2 -translate-x-1/2">
                <img src="{{ asset_version('images/amal-logo-black.png') }}" alt="{{ config('app.name') }}"
                    width="760" height="146" class="h-6 w-auto transition duration-500 sm:h-8"
                    :class="(! scrolled && ! open && overHero) ? 'brightness-0 invert' : ''">
            </a>

            {{-- Utilities (right) --}}
            <div class="flex items-center gap-4 sm:gap-5">
                <a href="/search" aria-label="{{ __('Search') }}" class="transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" aria-label="{{ __('Account') }}" class="hidden transition hover:text-accent sm:block">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>
                <a href="/cart" @click.prevent="$store.cart.openDrawer()" aria-label="{{ __('Cart') }}" class="relative transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                    <span x-show="$store.cart.count > 0" x-cloak x-text="$store.cart.count"
                        class="absolute -end-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-accent px-1 text-[10px] font-medium text-white"></span>
                </a>
            </div>
        </div>
    </div>

    {{-- Full-screen Menu overlay --}}
    <div x-show="open" x-cloak id="site-menu"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] flex flex-col bg-white text-ink">
        {{-- Overlay bar mirrors the header: Close · logo --}}
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6">
            <div class="relative flex h-20 items-center justify-between">
                <button @click="open = false"
                    class="flex items-center gap-2.5 p-1 text-[11px] uppercase tracking-[0.22em] transition hover:text-accent">
                    <span class="relative block h-4 w-6" aria-hidden="true">
                        <span class="absolute top-1/2 left-0 block h-px w-6 rotate-45 bg-current"></span>
                        <span class="absolute top-1/2 left-0 block h-px w-6 -rotate-45 bg-current"></span>
                    </span>
                    <span class="hidden sm:inline">{{ __('Close') }}</span>
                </button>
                <a href="/" aria-label="{{ config('app.name') }}" class="absolute left-1/2 -translate-x-1/2">
                    <img src="{{ asset_version('images/amal-logo-black.png') }}" alt="{{ config('app.name') }}"
                        width="760" height="146" class="h-6 w-auto sm:h-8">
                </a>
                <span class="w-6" aria-hidden="true"></span>
            </div>
        </div>

        {{-- Nav + utilities --}}
        <div class="flex-1 overflow-y-auto">
            <div class="mx-auto flex min-h-full max-w-7xl flex-col px-6 pb-10 pt-2 sm:px-10">
                <nav class="flex flex-col">
                    @foreach ($nav as $item)
                        <a href="{{ $item['url'] }}"
                            class="border-b border-stone-soft py-4 font-serif text-3xl text-ink transition hover:text-accent hover:ps-3 sm:py-5 sm:text-5xl {{ $isActive($item['url']) ? 'text-accent' : '' }}">
                            {{ __($item['label']) }}
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto flex flex-col gap-6 pt-10 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-6 text-xs uppercase tracking-[0.2em] text-ink/70">
                        <a href="/search" class="transition hover:text-accent">{{ __('Search') }}</a>
                        <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" class="transition hover:text-accent">{{ __('Account') }}</a>
                    </div>

                    {{-- Language + currency --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach (config('regions.locales') as $code => $loc)
                            <form method="POST" action="{{ route('preferences.update') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $code }}">
                                <button type="submit"
                                    class="border px-3 py-1.5 text-xs {{ app()->getLocale() === $code ? 'border-ink bg-ink text-white' : 'border-stone-soft text-ink/70' }}">
                                    {{ $loc['native'] }}
                                </button>
                            </form>
                        @endforeach
                        <form method="POST" action="{{ route('preferences.update') }}">
                            @csrf
                            <select name="currency" onchange="this.form.submit()" aria-label="{{ __('Currency') }}"
                                class="border border-stone-soft bg-sand/40 px-2 py-1.5 text-xs text-ink focus:border-accent focus:outline-none">
                                @foreach (config('regions.currencies') as $code => $cur)
                                    <option value="{{ $code }}" @selected(\App\Support\Money::currentCurrency() === $code)>{{ $code }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
