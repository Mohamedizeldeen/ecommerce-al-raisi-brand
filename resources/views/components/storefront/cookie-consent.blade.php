<div x-data="cookieConsent" x-show="show" x-cloak
    x-transition:enter="transition duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
    x-transition:enter-start="translate-y-6 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition duration-200 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-24 left-4 right-4 z-[95] rounded-2xl border border-stone-soft bg-white p-5 shadow-2xl sm:bottom-6 sm:left-6 sm:right-auto sm:max-w-sm">
    <p class="font-serif text-lg text-ink">We value your privacy</p>
    <p class="mt-2 text-sm leading-relaxed text-stone-600">
        We use cookies to enhance your experience and understand what you love, so we can serve you better.
        <a href="{{ route('pages.privacy') }}" class="text-accent underline transition hover:text-accent-dark">Learn more</a>.
    </p>
    <div class="mt-4 flex items-center gap-2">
        <button @click="accept()"
            class="flex-1 bg-ink px-4 py-2.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
            Accept
        </button>
        <button @click="decline()"
            class="px-4 py-2.5 text-xs uppercase tracking-[0.2em] text-stone-500 transition hover:text-ink">
            Decline
        </button>
    </div>
</div>
