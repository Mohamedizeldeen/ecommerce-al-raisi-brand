<x-layouts.storefront :title="__('Collections')">
    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-16">
        <header class="mb-14 text-center">
            <h1 class="text-4xl text-ink">{{ __('Collections') }}</h1>
            <p class="mt-3 text-stone-500">{{ __('Browse by season and capsule.') }}</p>
        </header>

        @foreach ($collections as $type => $group)
            <div class="mb-16">
                <h2 class="mb-8 border-b border-stone-soft pb-3 text-2xl text-ink">{{ __($type) }}</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($group as $collection)
                        <div class="reveal" x-data x-intersect.once="$el.classList.add('is-visible')"
                            style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                            <a href="{{ route('collections.show', $collection) }}"
                                class="group relative block aspect-[3/2] overflow-hidden bg-stone-soft">
                                <img src="{{ $collection->coverImageUrl($loop->index) }}" alt="{{ $collection->name }}"
                                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
                                <div class="absolute inset-0 bg-ink/40 transition duration-500 group-hover:bg-ink/55"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center text-white">
                                    <p class="text-xs uppercase tracking-[0.25em] text-white/80">{{ $collection->season ?? $collection->year }}</p>
                                    <h3 class="mt-2 font-serif text-3xl">{{ $collection->name }}</h3>
                                    <p class="mt-1 text-xs text-white/70">{{ $collection->products_count }} {{ __('pieces') }}</p>
                                </div>
                            </a>
                            {{-- The story of the collection, before you step inside. --}}
                            <div class="mt-4">
                                <p class="text-sm leading-relaxed text-stone-600">
                                    {{ $collection->description ? \Illuminate\Support\Str::limit($collection->description, 150) : __('A considered edit of pieces from the :name collection.', ['name' => $collection->name]) }}
                                </p>
                                <a href="{{ route('collections.show', $collection) }}"
                                    class="mt-2 inline-block text-xs uppercase tracking-[0.2em] text-accent transition hover:text-accent-dark">
                                    {{ __('Discover the story →') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
</x-layouts.storefront>
