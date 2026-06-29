@props([
    'post',
    'related',
    'indexRoute',
    'showRoute',
    'backLabel',
    'moreLabel',
])
<article class="mx-auto max-w-3xl px-4 sm:px-6 py-12">
    <a href="{{ route($indexRoute) }}" class="text-xs uppercase tracking-[0.18em] text-accent link-underline"><span class="inline-block rtl:-scale-x-100" aria-hidden="true">&larr;</span> {{ $backLabel }}</a>

    <header class="mt-6 text-center">
        @if ($post->published_at)
            <p class="text-xs uppercase tracking-[0.25em] text-accent">{{ $post->published_at->translatedFormat('d F Y') }}</p>
        @endif
        <h1 class="mt-3 text-4xl leading-tight text-ink lg:text-5xl">{{ $post->title }}</h1>
        @if ($post->excerpt)
            <p class="mx-auto mt-4 max-w-2xl leading-relaxed text-stone-500">{{ $post->excerpt }}</p>
        @endif
    </header>

    @if ($post->coverImageUrl())
        <div class="mt-8 aspect-[16/9] overflow-hidden bg-sand">
            <img src="{{ $post->coverImageUrl() }}" alt="{{ $post->title }}" class="h-full w-full object-cover">
        </div>
    @endif

    @if ($post->body)
        <div class="mx-auto mt-10 max-w-none leading-relaxed text-stone-700
            [&_p]:mb-5 [&_h2]:mt-8 [&_h2]:mb-3 [&_h2]:font-serif [&_h2]:text-2xl [&_h2]:text-ink
            [&_h3]:mt-6 [&_h3]:mb-2 [&_h3]:text-xl [&_h3]:text-ink
            [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-6
            [&_a]:text-accent [&_a]:underline [&_img]:my-6 [&_img]:w-full
            [&_blockquote]:my-6 [&_blockquote]:border-s-2 [&_blockquote]:border-accent [&_blockquote]:ps-4 [&_blockquote]:italic">
            {!! $post->body !!}
        </div>
    @endif

    {{-- Shop this article — commercial products linked to the post (SDM) --}}
    @if ($post->products->isNotEmpty())
        <div class="mt-12 border-t border-stone-soft pt-8">
            <h2 class="mb-6 text-center font-serif text-2xl text-ink">{{ __('Shop this article') }}</h2>
            <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3">
                @foreach ($post->products as $product)
                    <x-storefront.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-10 border-t border-stone-soft pt-6">
        <x-storefront.share :title="$post->title" />
    </div>
</article>

@if ($related->isNotEmpty())
    <section class="bg-sand py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2 class="mb-8 text-center text-2xl text-ink">{{ $moreLabel }}</h2>
            <div class="grid gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $post)
                    <x-storefront.post-card :post="$post" :route="$showRoute" />
                @endforeach
            </div>
        </div>
    </section>
@endif
