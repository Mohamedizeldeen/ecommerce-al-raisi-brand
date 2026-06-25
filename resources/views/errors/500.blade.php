<x-layouts.storefront title="{{ __('Something went wrong') }}">
    <section class="mx-auto flex max-w-2xl flex-col items-center px-4 sm:px-6 py-32 text-center">
        <h1 class="text-5xl text-ink sm:text-6xl">{{ __('Something went wrong') }}</h1>
        <p class="mt-6 leading-relaxed text-stone-600">{{ __('An unexpected error occurred. Please try again later.') }}</p>

        <div class="mt-12">
            <a href="{{ route('home') }}"
                class="bg-ink px-9 py-4 text-xs uppercase tracking-[0.25em] text-white transition hover:bg-accent">
                {{ __('Back to home') }}
            </a>
        </div>
    </section>
</x-layouts.storefront>
