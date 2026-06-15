@props(['title' => null, 'description' => null])
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name').' — Omani Fashion House' }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-white">
    <x-storefront.header />

    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="{{ session('error') ? 'bg-red-600' : 'bg-ink' }} px-4 py-3 text-center text-sm text-white">
            <span>{{ session('success') ?? session('error') }}</span>
            <button @click="show = false" class="ml-3 text-xs underline">Dismiss</button>
        </div>
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront.footer />

    <x-storefront.age-gate />

    @stack('scripts')
</body>
</html>
