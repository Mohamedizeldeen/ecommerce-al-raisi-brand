@props(['url' => null, 'title' => ''])
@php
    $url = $url ?: url()->current();
    $enc = rawurlencode($url);
    $encTitle = rawurlencode($title);
    $btn = 'flex h-9 w-9 items-center justify-center rounded-full border border-stone-soft text-ink transition hover:border-ink hover:bg-ink hover:text-white';
@endphp
<div class="flex flex-wrap items-center gap-3" x-data="{ copied: false }">
    <span class="text-xs uppercase tracking-[0.18em] text-stone-500">{{ __('Share') }}</span>

    <a href="https://wa.me/?text={{ $encTitle }}%20{{ $enc }}" target="_blank" rel="noopener"
        aria-label="WhatsApp" class="{{ $btn }}">
        <x-storefront.icon name="whatsapp" class="h-4 w-4" />
    </a>
    <a href="https://twitter.com/intent/tweet?text={{ $encTitle }}&url={{ $enc }}" target="_blank" rel="noopener"
        aria-label="X" class="{{ $btn }}">
        <x-storefront.icon name="x" class="h-4 w-4" />
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $enc }}" target="_blank" rel="noopener"
        aria-label="Facebook" class="{{ $btn }}">
        <x-storefront.icon name="facebook" class="h-4 w-4" />
    </a>

    <button type="button" aria-label="{{ __('Copy link') }}" class="{{ $btn }}"
        @click="navigator.clipboard.writeText(@js($url)); copied = true; setTimeout(() => copied = false, 1500)">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
        </svg>
    </button>
    <span x-show="copied" x-cloak class="text-xs text-accent">{{ __('Copied') }}</span>
</div>
