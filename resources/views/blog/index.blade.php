<x-layouts.storefront :title="__('Blog')"
    :description="__('Stories, news and notes from the house of Amal Al Raisi.')">

    <section class="bg-ink text-white">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 py-20 text-center">
            <p class="text-xs uppercase tracking-[0.3em] text-accent">{{ __('Journal') }}</p>
            <h1 class="mt-4 text-4xl lg:text-6xl">{{ __('Blog') }}</h1>
            <p class="mx-auto mt-5 max-w-2xl leading-relaxed text-white/70">
                {{ __('Stories, news and notes from the house of Amal Al Raisi.') }}
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 sm:px-6 py-16">
        @if ($posts->isEmpty())
            <p class="text-center text-stone-500">{{ __('New stories are coming soon.') }}</p>
        @else
            <div class="grid gap-x-8 gap-y-14 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <div class="reveal" x-data x-intersect.once="$el.classList.add('is-visible')"
                        style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                        <x-storefront.post-card :post="$post" route="blog.show" />
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.storefront>
