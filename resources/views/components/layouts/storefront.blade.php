@props(['title' => null, 'description' => null, 'image' => null, 'noindex' => false, 'ogType' => 'website'])
@php($cartCount = app(\App\Services\CartService::class)->count())
@php($locale = app()->getLocale())
@php($dir = config('regions.locales.'.$locale.'.dir', 'ltr'))
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($pageTitle = $title ? $title.' — '.config('app.name') : config('app.name').' — '.__('Omani Fashion House'))
    @php($ogImage = $image ?: asset_version('images/heroes/hero.jpg'))
    <title>{{ $pageTitle }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    @if ($noindex)
        <meta name="robots" content="noindex,nofollow">
    @endif

    {{-- Canonical + hreflang alternates --}}
    <link rel="canonical" href="{{ url()->current() }}">
    @foreach (config('regions.locales', []) as $localeCode => $localeMeta)
        <link rel="alternate" hreflang="{{ $localeCode }}" href="{{ url()->current() }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    @if ($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    @if ($description)
        <meta name="twitter:description" content="{{ $description }}">
    @endif
    <meta name="twitter:image" content="{{ $ogImage }}">

    @stack('head')

    <script>
        document.documentElement.classList.add('js');
        window.__cartCount = {{ $cartCount }};
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-[200] focus:top-2 focus:start-2 focus:rounded focus:bg-ink focus:px-4 focus:py-2 focus:text-white">{{ __('Skip to content') }}</a>
    <x-storefront.announcement />
    <x-storefront.header />

    <main id="main" tabindex="-1" class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    <x-storefront.toasts />
    <x-storefront.cart-drawer />
    <x-storefront.assistant />
    <x-storefront.cookie-consent />
    <x-storefront.welcome-offer />

    @if (session('success') || session('error'))
        @push('scripts')
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.store('toast').push(@js(session('success') ?? session('error')), '{{ session('error') ? 'error' : 'success' }}');
                });
            </script>
        @endpush
    @endif

    @stack('scripts')
</body>
</html>
