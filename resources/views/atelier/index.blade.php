<x-layouts.storefront :title="__('The Atelier')"
    :description="__('Behind the scenes at Amal Al Raisi — fashion-show films, fittings and the making of each collection.')">

    <section class="bg-ink text-white">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 py-24 text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Behind the Scenes') }}</p>
            <h1 class="mt-4 text-4xl lg:text-6xl">{{ __('The Atelier') }}</h1>
            <p class="mx-auto mt-5 max-w-2xl leading-relaxed text-white/70">
                {{ __('Step inside the house — from the cutting table to the runway. Films, fittings and the making of each collection.') }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl space-y-20 px-4 sm:px-6 py-20">
        @forelse ($showcases as $i => $showcase)
            <article class="reveal grid items-center gap-10 lg:grid-cols-2" x-data
                x-intersect.once="$el.classList.add('is-visible')">
                {{-- Media --}}
                <div class="{{ $i % 2 ? 'lg:order-2' : '' }}">
                    @if ($showcase->embedUrl())
                        <div class="aspect-video overflow-hidden bg-stone-soft">
                            <iframe src="{{ $showcase->embedUrl() }}" class="h-full w-full" loading="lazy"
                                title="{{ $showcase->title }}" allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    @else
                        <div class="aspect-[4/3] overflow-hidden bg-stone-soft">
                            <img src="{{ $showcase->coverImageUrl($i) }}" alt="{{ $showcase->title }}"
                                class="h-full w-full object-cover">
                        </div>
                    @endif
                </div>

                {{-- Text --}}
                <div class="{{ $i % 2 ? 'lg:order-1' : '' }}">
                    <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $showcase->type_label }}</p>
                    <h2 class="mt-3 text-3xl text-ink">{{ $showcase->title }}</h2>
                    @if ($showcase->subtitle)
                        <p class="mt-1 text-stone-500">{{ $showcase->subtitle }}</p>
                    @endif
                    @if ($showcase->description)
                        <p class="mt-4 leading-relaxed text-stone-600">{{ $showcase->description }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p class="text-center text-stone-500">{{ __('Our atelier films and stories are coming soon.') }}</p>
        @endforelse
    </section>
</x-layouts.storefront>
