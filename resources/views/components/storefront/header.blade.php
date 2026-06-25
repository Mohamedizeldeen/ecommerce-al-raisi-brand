@php
    $nav = [
        ['label' => 'Collections', 'url' => '/collections'],
        ['label' => 'Ready-to-Wear', 'url' => '/category/ready-to-wear'],
        ['label' => 'Accessories', 'url' => '/category/accessories'],
        ['label' => 'Lifestyle', 'url' => '/category/lifestyle'],
        ['label' => 'The Atelier', 'url' => '/atelier'],
        ['label' => 'Blog', 'url' => '/blog'],
        ['label' => 'Press', 'url' => '/press'],
        ['label' => 'About', 'url' => '/about'],
        ['label' => 'Contact', 'url' => '/contact'],
    ];

    // Active when the current request path matches the item's URL (leading slash stripped).
    $isActive = fn (string $url) => request()->is(ltrim($url, '/'));
@endphp

<header x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 24"
    class="sticky top-0 z-50 transition-all duration-500"
    :class="scrolled ? 'bg-white/85 shadow-[0_1px_0_rgba(22,19,15,0.08)] backdrop-blur-md' : 'bg-white'">
    <div class="mx-auto max-w-7xl px-3 sm:px-6">
        <div class="flex h-20 items-center justify-between gap-3 sm:gap-4">
            <button @click="open = ! open" :aria-expanded="open" aria-controls="mobile-menu" class="-ml-2 p-2 text-ink xl:hidden" aria-label="{{ __('Toggle menu') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>

            <a href="/" aria-label="{{ config('app.name') }}" class="shrink-0">
                <img src="{{ asset_version('images/amal-logo-font-black.png') }}" alt="{{ config('app.name') }}"
                    width="760" height="146" class="h-7 w-auto sm:h-9">
            </a>

            <nav class="hidden items-center gap-6 text-[11px] uppercase tracking-[0.18em] text-ink/80 xl:flex">
                @foreach ($nav as $item)
                    <a href="{{ $item['url'] }}" @if ($isActive($item['url'])) aria-current="page" @endif
                        class="link-underline whitespace-nowrap hover:text-accent {{ $isActive($item['url']) ? 'text-accent [background-size:100%_1px]' : '' }}">{{ __($item['label']) }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3 text-ink sm:gap-5">
                <div class="hidden sm:block">
                    <x-storefront.region-switcher />
                </div>
                <a href="/search" aria-label="{{ __('Search') }}" class="transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>
                <a href="{{ auth()->check() ? route('account.dashboard') : route('login') }}" aria-label="{{ __('Account') }}" class="transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </a>
                <a href="/cart" @click.prevent="$store.cart.openDrawer()" aria-label="{{ __('Cart') }}" class="relative transition hover:text-accent">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                    <span x-show="$store.cart.count > 0" x-cloak x-text="$store.cart.count"
                        class="absolute -end-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-ink px-1 text-[10px] font-medium text-white"></span>
                </a>
            </div>
        </div>
    </div>

    <div x-show="open" x-cloak id="mobile-menu" x-transition.origin.top.duration.300ms class="border-t border-stone-soft bg-white xl:hidden">
        <nav class="flex flex-col px-4 py-3 text-sm uppercase tracking-[0.15em] text-ink/80">
            @foreach ($nav as $item)
                <a href="{{ $item['url'] }}" @if ($isActive($item['url'])) aria-current="page" @endif
                    class="py-2 hover:text-accent {{ $isActive($item['url']) ? 'text-accent underline underline-offset-4' : '' }}">{{ __($item['label']) }}</a>
            @endforeach
        </nav>

        {{-- Language + currency (phones only — header switcher is hidden below sm) --}}
        <div class="flex flex-wrap items-center gap-2 border-t border-stone-soft px-4 py-3 sm:hidden">
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
            <form method="POST" action="{{ route('preferences.update') }}" class="ms-auto">
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
</header>
