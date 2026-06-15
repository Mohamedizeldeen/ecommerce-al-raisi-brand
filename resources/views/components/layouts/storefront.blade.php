@props(['title' => null, 'description' => null])
@php($cartCount = app(\App\Services\CartService::class)->count())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name').' — Omani Fashion House' }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
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
