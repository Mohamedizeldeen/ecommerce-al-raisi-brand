@php($dir = config('regions.locales.'.app()->getLocale().'.dir', 'ltr'))
{{-- In RTL the bag icon sits on the left, so the drawer anchors to the inline-end
     (left) and slides in from there; in LTR it anchors right and slides from right. --}}
@php($offscreen = $dir === 'rtl' ? '-translate-x-full' : 'translate-x-full')
<div x-data x-cloak>
    <div x-show="$store.cart.open" x-transition.opacity.duration.300ms
        @click="$store.cart.close()" class="fixed inset-0 z-[80] bg-ink/40"></div>

    <aside x-show="$store.cart.open"
        x-transition:enter="transition duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        x-transition:enter-start="{{ $offscreen }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition duration-300 ease-in"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $offscreen }}"
        @keydown.escape.window="$store.cart.close()"
        x-trap.noscroll="$store.cart.open"
        role="dialog" aria-modal="true" aria-label="{{ __('Your Bag') }}"
        class="fixed inset-y-0 end-0 z-[90] flex w-full max-w-md flex-col bg-white shadow-2xl">

        <header class="flex items-center justify-between border-b border-stone-soft px-6 py-5">
            <h2 class="text-xs uppercase tracking-[0.25em] text-ink">
                {{ __('Your Bag') }} <span class="text-stone-400" x-text="'(' + $store.cart.count + ')'"></span>
            </h2>
            <button @click="$store.cart.close()" aria-label="{{ __('Close') }}" class="text-ink transition hover:text-accent">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div class="relative min-h-0 flex-1">
            <div x-show="$store.cart.loading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/70">
                <span class="text-[11px] uppercase tracking-[0.25em] text-stone-400">{{ __('Loading…') }}</span>
            </div>
            <div class="h-full" x-html="$store.cart.html"></div>
        </div>
    </aside>
</div>
