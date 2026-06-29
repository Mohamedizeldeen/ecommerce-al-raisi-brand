<x-layouts.storefront title="{{ __('Page not found') }}">
    <section class="mx-auto flex max-w-2xl flex-col items-center px-4 sm:px-6 py-32 text-center">
        <h1 class="text-5xl text-ink sm:text-6xl">{{ __('Page not found') }}</h1>
        <p class="mt-6 leading-relaxed text-stone-600">{{ __('The page you are looking for could not be found.') }}</p>

        <div class="mt-12 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('home') }}"
                class="bg-ink px-9 py-4 text-xs uppercase tracking-[0.25em] text-white transition hover:bg-accent">
                {{ __('Back to home') }}
            </a>
            <a href="{{ route('shop.index') }}"
                class="border border-ink px-9 py-4 text-xs uppercase tracking-[0.25em] text-ink transition hover:bg-ink hover:text-white">
                {{ __('Shop') }}
            </a>
        </div>
    </section>
</x-layouts.storefront>
