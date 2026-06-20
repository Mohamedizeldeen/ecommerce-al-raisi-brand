@props(['post', 'route'])
@php $cover = $post->coverImageUrl(); @endphp
<a href="{{ route($route, $post) }}" class="group block">
    <div class="aspect-[4/3] overflow-hidden bg-sand">
        @if ($cover)
            <img src="{{ $cover }}" alt="{{ $post->title }}"
                class="h-full w-full object-cover transition-transform duration-[1200ms] ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105">
        @else
            <div class="flex h-full w-full items-center justify-center text-stone-300">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 19.5h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z" />
                </svg>
            </div>
        @endif
    </div>
    <div class="mt-4">
        @if ($post->published_at)
            <p class="text-xs uppercase tracking-[0.2em] text-accent">{{ $post->published_at->translatedFormat('d M Y') }}</p>
        @endif
        <h3 class="mt-2 font-serif text-xl text-ink transition group-hover:text-accent">{{ $post->title }}</h3>
        @if ($post->summary(120))
            <p class="mt-2 text-sm leading-relaxed text-stone-600">{{ $post->summary(120) }}</p>
        @endif
        <span class="mt-3 inline-block text-[11px] uppercase tracking-[0.18em] text-accent link-underline">{{ __('Read more') }} &rarr;</span>
    </div>
</a>
