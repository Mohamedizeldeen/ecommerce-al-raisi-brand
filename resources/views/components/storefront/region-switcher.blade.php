@php
    $locales = config('regions.locales');
    $currencies = config('regions.currencies');
    $currentLocale = app()->getLocale();
    $currentCurrency = \App\Support\Money::currentCurrency();
@endphp
<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button @click="open = ! open" :aria-expanded="open" aria-label="{{ __('Language & currency') }}"
        class="flex items-center gap-1.5 text-[11px] uppercase tracking-[0.18em] text-ink/80 transition hover:text-accent">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.95 8.95 0 0 0 4.5-1.207M12 21a8.95 8.95 0 0 1-4.5-1.207M3.6 9h16.8M3.6 15h16.8M12 3a13.5 13.5 0 0 0 0 18 13.5 13.5 0 0 0 0-18Z" />
        </svg>
        <span>{{ $currentCurrency }}</span>
    </button>

    <div x-show="open" x-cloak @click.outside="open = false"
        x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute end-0 z-50 mt-3 w-64 rounded-xl border border-stone-soft bg-white p-4 text-start shadow-xl">
        <p class="text-[10px] uppercase tracking-[0.2em] text-stone-400">{{ __('Language') }}</p>
        <div class="mt-2 flex gap-2">
            @foreach ($locales as $code => $loc)
                <form method="POST" action="{{ route('preferences.update') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button type="submit"
                        class="w-full border px-3 py-2 text-xs transition {{ $currentLocale === $code ? 'border-ink bg-ink text-white' : 'border-stone-soft hover:border-ink' }}">
                        {{ $loc['native'] }}
                    </button>
                </form>
            @endforeach
        </div>

        <p class="mt-4 text-[10px] uppercase tracking-[0.2em] text-stone-400">{{ __('Currency') }}</p>
        <form method="POST" action="{{ route('preferences.update') }}" class="mt-2">
            @csrf
            <select name="currency" onchange="this.form.submit()"
                class="w-full border border-stone-soft bg-sand/40 px-3 py-2 text-sm text-ink focus:border-accent focus:outline-none">
                @foreach ($currencies as $code => $cur)
                    <option value="{{ $code }}" @selected($currentCurrency === $code)>{{ $code }} — {{ $cur['name'] }}</option>
                @endforeach
            </select>
        </form>
        <p class="mt-3 text-[11px] leading-relaxed text-stone-400">{{ __('Orders are charged in OMR.') }}</p>
    </div>
</div>
