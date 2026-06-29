<x-layouts.storefront :title="__('Occasions')" :description="__('Dress for the moment — Amal Al Raisi edits for weddings, Eid and Ramadan, resort and every occasion.')">
    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-16">
        <header class="mb-14 text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Occasions') }}</p>
            <h1 class="mt-3 text-4xl text-ink">{{ __('Shop by Occasion') }}</h1>
            <p class="mx-auto mt-3 max-w-xl text-stone-500">{{ __('Curated edits for the moments that matter.') }}</p>
        </header>

        @if ($occasions->isEmpty())
            <p class="text-center text-stone-500">{{ __('New occasion edits are coming soon.') }}</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($occasions as $occasion)
                    <a href="{{ route('occasions.show', $occasion->slug) }}"
                        class="reveal group relative block aspect-[3/2] overflow-hidden bg-stone-soft"
                        x-data x-intersect.once="$el.classList.add('is-visible')"
                        style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                        <img src="{{ $occasion->coverImageUrl($loop->index) }}" alt="{{ $occasion->name }}" loading="lazy"
                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                        <div class="absolute inset-0 bg-ink/40 transition duration-500 group-hover:bg-ink/55"></div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center text-white">
                            <h2 class="font-serif text-3xl">{{ $occasion->name }}</h2>
                            <p class="mt-1 text-xs text-white/70">{{ $occasion->products_count }} {{ __('pieces') }}</p>
                            <span class="mt-3 inline-block text-[11px] uppercase tracking-[0.2em] opacity-0 transition duration-500 group-hover:opacity-100">{{ __('Explore') }} <span class="inline-block rtl:-scale-x-100" aria-hidden="true">&rarr;</span></span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.storefront>
