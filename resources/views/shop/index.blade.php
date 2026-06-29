<x-layouts.storefront :title="__('Shop')" :description="__('Shop Amal Al Raisi by category — kaftans, evening and occasion dresses, maxi dresses, jumpsuits, sets, abayas and more.')">
    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-16">
        <header class="mb-14 text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Shop') }}</p>
            <h1 class="mt-3 text-4xl text-ink">{{ __('Shop by Category') }}</h1>
            <p class="mx-auto mt-3 max-w-xl text-stone-500">{{ __('Browse our evergreen edit — by the pieces you’re looking for.') }}</p>
        </header>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category->slug) }}"
                    class="reveal group relative block aspect-[3/2] overflow-hidden bg-stone-soft"
                    x-data x-intersect.once="$el.classList.add('is-visible')"
                    style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                    <img src="{{ $category->coverImageUrl($loop->index) }}" alt="{{ $category->name }}" loading="lazy"
                        class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                    <div class="absolute inset-0 bg-ink/40 transition duration-500 group-hover:bg-ink/55"></div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center text-white">
                        <h2 class="font-serif text-3xl">{{ $category->name }}</h2>
                        @if ($category->children->isNotEmpty())
                            <p class="mt-2 text-[11px] uppercase tracking-[0.2em] text-white/70">
                                {{ $category->children->pluck('name')->take(3)->implode(' · ') }}
                            </p>
                        @endif
                        <span class="mt-3 inline-block text-[11px] uppercase tracking-[0.2em] opacity-0 transition duration-500 group-hover:opacity-100">{{ __('Explore') }} <span class="inline-block rtl:-scale-x-100" aria-hidden="true">&rarr;</span></span>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($occasions->isNotEmpty())
            <div class="mt-20">
                <div class="mb-8 flex items-end justify-between border-b border-stone-soft pb-3">
                    <h2 class="text-2xl text-ink">{{ __('Shop by Occasion') }}</h2>
                    <a href="{{ route('occasions.index') }}" class="text-xs uppercase tracking-[0.18em] text-accent link-underline">{{ __('All occasions') }}</a>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach ($occasions as $occasion)
                        <a href="{{ route('occasions.show', $occasion->slug) }}"
                            class="border border-stone-soft px-5 py-2.5 text-xs uppercase tracking-[0.18em] text-ink/80 transition hover:border-accent hover:text-accent">
                            {{ $occasion->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</x-layouts.storefront>
