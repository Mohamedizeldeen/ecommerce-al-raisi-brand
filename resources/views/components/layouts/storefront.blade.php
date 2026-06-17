@props(['title' => null, 'description' => null, 'image' => null])
@php($cartCount = app(\App\Services\CartService::class)->count())
@php($locale = app()->getLocale())
@php($dir = config('regions.locales.'.$locale.'.dir', 'ltr'))
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($pageTitle = $title ? $title.' — '.config('app.name') : config('app.name').' — Omani Fashion House')
    <title>{{ $pageTitle }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
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
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    @if ($description)
        <meta name="twitter:description" content="{{ $description }}">
    @endif
    @if ($image)
        <meta name="twitter:image" content="{{ $image }}">
    @endif

    @stack('head')

    <script>
        document.documentElement.classList.add('js');
        window.__cartCount = {{ $cartCount }};
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white">
    <x-storefront.announcement />
    <x-storefront.header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    <x-storefront.toasts />
    <x-storefront.cart-drawer />
    <x-storefront.age-gate />
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
