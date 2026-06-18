@php
    $social = [
        ['label' => 'Instagram', 'url' => '#'],
        ['label' => 'Facebook', 'url' => '#'],
        ['label' => 'TikTok', 'url' => '#'],
        ['label' => 'YouTube', 'url' => '#'],
        ['label' => 'Pinterest', 'url' => '#'],
    ];
@endphp

<footer class="mt-24 bg-ink text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-16">
        <div class="grid gap-12 lg:grid-cols-4">
            <div class="lg:col-span-1">
                {{-- Grey-on-black signature; mix-blend-screen drops the black box so only
                     the signature shows on the dark footer. --}}
                <img src="{{ asset_version('images/amal-logo.avif') }}" alt="{{ config('app.name') }}"
                    width="540" height="104" class="h-12 w-auto mix-blend-screen">
                <p class="mt-4 text-sm leading-relaxed text-white/60">
                    {{ __('Omani fashion house celebrating heritage through contemporary design since 2006.') }}
                </p>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-[0.2em] text-white/50">{{ __('Shop') }}</h4>
                <ul class="mt-4 space-y-2 text-sm text-white/80">
                    <li><a href="/collections" class="hover:text-accent">{{ __('Collections') }}</a></li>
                    <li><a href="/category/ready-to-wear" class="hover:text-accent">{{ __('Ready-to-Wear') }}</a></li>
                    <li><a href="/category/accessories" class="hover:text-accent">{{ __('Accessories') }}</a></li>
                    <li><a href="/category/lifestyle" class="hover:text-accent">{{ __('Lifestyle') }}</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-[0.2em] text-white/50">{{ __('Information') }}</h4>
                <ul class="mt-4 space-y-2 text-sm text-white/80">
                    <li><a href="/about" class="hover:text-accent">{{ __('About Us') }}</a></li>
                    <li><a href="/size-guide" class="hover:text-accent">{{ __('Size Guide') }}</a></li>
                    <li><a href="/contact" class="hover:text-accent">{{ __('Contact') }}</a></li>
                    <li><a href="/pages/shipping-returns" class="hover:text-accent">{{ __('Shipping & Returns') }}</a></li>
                    <li><a href="/pages/privacy-policy" class="hover:text-accent">{{ __('Privacy Policy') }}</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-[0.2em] text-white/50">{{ __('Newsletter') }}</h4>
                <p class="mt-4 text-sm text-white/70">{{ __('Subscribe for 10% off your first order.') }}</p>
                <form action="/newsletter" method="POST" class="mt-4 flex">
                    @csrf
                    <input type="email" name="email" required placeholder="{{ __('Email address') }}"
                        class="w-full bg-transparent border border-white/30 px-3 py-2 text-sm placeholder-white/40 focus:border-accent focus:outline-none">
                    <button type="submit" class="border border-l-0 border-white/30 px-4 text-xs uppercase tracking-widest hover:bg-accent hover:border-accent transition">{{ __('Join') }}</button>
                </form>
                <div class="mt-8 text-sm text-white/60">
                    <p>{{ __('Al Athaiba, Muscat') }}</p>
                    <p>{{ __('Sultanate of Oman') }}</p>
                    <p class="mt-2">{{ __('Thu–Sat · 9am–1pm & 4pm–9pm') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-white/40">&copy; {{ now()->year }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
            <ul class="flex flex-wrap gap-x-5 gap-y-2 text-xs uppercase tracking-[0.15em] text-white/60">
                @foreach ($social as $item)
                    <li><a href="{{ $item['url'] }}" class="hover:text-accent">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>
