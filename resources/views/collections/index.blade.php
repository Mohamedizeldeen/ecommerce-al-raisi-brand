<x-layouts.storefront title="Collections">
    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-16">
        <header class="mb-14 text-center">
            <h1 class="text-4xl text-ink">Collections</h1>
            <p class="mt-3 text-stone-500">Browse by season and capsule.</p>
        </header>

        @foreach ($collections as $type => $group)
            <div class="mb-16">
                <h2 class="mb-8 border-b border-stone-soft pb-3 text-2xl text-ink">{{ $type }}</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($group as $collection)
                        <a href="{{ route('collections.show', $collection) }}"
                            class="group relative block aspect-[3/2] overflow-hidden bg-sand">
                            <div class="flex h-full w-full items-center justify-center text-center">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $collection->season ?? $collection->year }}</p>
                                    <h3 class="mt-2 font-serif text-2xl text-ink transition group-hover:text-accent">{{ $collection->name }}</h3>
                                    <p class="mt-1 text-xs text-stone-500">{{ $collection->products_count }} pieces</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>
</x-layouts.storefront>
