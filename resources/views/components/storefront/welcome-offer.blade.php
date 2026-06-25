@php
    $percent = (int) \App\Models\Setting::get('newsletter_discount_percent', 10);
    $code = 'WELCOME'.$percent;
@endphp
<div x-data="welcomeOffer(@js($code))" x-cloak>
    {{-- Overlay --}}
    <div x-show="show" x-transition.opacity.duration.300ms @click="dismiss()"
        class="fixed inset-0 z-[110] bg-ink/60"></div>

    {{-- Modal --}}
    <div x-show="show"
        x-transition:enter="transition duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        x-transition:enter-start="translate-y-4 opacity-0 sm:scale-95"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        @keydown.escape.window="dismiss()"
        x-trap.noscroll="show"
        role="dialog" aria-modal="true" aria-label="{{ $percent }}% {{ __('off your first order') }}"
        class="fixed left-1/2 top-1/2 z-[120] w-[calc(100vw-2.5rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-8 text-center shadow-2xl sm:p-10">
        <button @click="dismiss()" aria-label="{{ __('Close') }}"
            class="absolute right-4 top-4 text-stone-400 transition hover:text-ink">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>

        <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ config('app.name') }}</p>
        <h2 class="mt-3 font-serif text-3xl text-ink sm:text-4xl">{{ $percent }}% {{ __('off your first order') }}</h2>
        <p class="mx-auto mt-3 max-w-sm text-sm leading-relaxed text-stone-600">
            {{ __('Join our list for early access to new collections — and enjoy :percent% off your first purchase.', ['percent' => $percent]) }}
        </p>

        <form method="POST" action="{{ route('newsletter.subscribe') }}" @submit="markSeen()" class="mt-6 flex">
            @csrf
            <input type="email" name="email" required placeholder="{{ __('Email address') }}" aria-label="{{ __('Email address') }}" autocomplete="email"
                class="w-full min-w-0 border border-stone-soft bg-sand/40 px-4 py-3 text-sm text-ink placeholder-stone-400 focus:border-accent focus:outline-none">
            <button type="submit"
                class="shrink-0 bg-ink px-5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                {{ __('Get code') }}
            </button>
        </form>

        <div class="mt-5 flex items-center justify-center gap-2 text-sm text-stone-500">
            <span>{{ __('Or use code') }}</span>
            <button @click="copy()" type="button"
                class="rounded border border-dashed border-accent/50 px-2 py-0.5 font-mono text-accent transition hover:bg-accent/5">
                <span x-text="code"></span>
                <span x-show="copied" class="ml-1 not-italic">✓</span>
            </button>
            <span>{{ __('at checkout') }}</span>
        </div>
    </div>
</div>
