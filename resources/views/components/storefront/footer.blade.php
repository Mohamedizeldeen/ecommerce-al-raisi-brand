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
                <h3 class="font-serif text-2xl uppercase tracking-wide">{{ config('app.name') }}</h3>
                <p class="mt-4 text-sm leading-relaxed text-white/60">
                    Omani fashion house celebrating heritage through contemporary design since 2006.
                </p>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-[0.2em] text-white/50">Shop</h4>
                <ul class="mt-4 space-y-2 text-sm text-white/80">
                    <li><a href="/collections" class="hover:text-accent">Collections</a></li>
                    <li><a href="/category/ready-to-wear" class="hover:text-accent">Ready-to-Wear</a></li>
                    <li><a href="/category/accessories" class="hover:text-accent">Accessories</a></li>
                    <li><a href="/category/lifestyle" class="hover:text-accent">Lifestyle</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-[0.2em] text-white/50">Information</h4>
                <ul class="mt-4 space-y-2 text-sm text-white/80">
                    <li><a href="/about" class="hover:text-accent">About Us</a></li>
                    <li><a href="/size-guide" class="hover:text-accent">Size Guide</a></li>
                    <li><a href="/contact" class="hover:text-accent">Contact</a></li>
                    <li><a href="/pages/shipping-returns" class="hover:text-accent">Shipping &amp; Returns</a></li>
                    <li><a href="/pages/privacy-policy" class="hover:text-accent">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-[0.2em] text-white/50">Newsletter</h4>
                <p class="mt-4 text-sm text-white/70">Subscribe for 10% off your first order.</p>
                <form action="/newsletter" method="POST" class="mt-4 flex">
                    @csrf
                    <input type="email" name="email" required placeholder="Email address"
                        class="w-full bg-transparent border border-white/30 px-3 py-2 text-sm placeholder-white/40 focus:border-accent focus:outline-none">
                    <button type="submit" class="border border-l-0 border-white/30 px-4 text-xs uppercase tracking-widest hover:bg-accent hover:border-accent transition">Join</button>
                </form>
                <div class="mt-8 text-sm text-white/60">
                    <p>Al Athaiba, Muscat</p>
                    <p>Sultanate of Oman</p>
                    <p class="mt-2">Thu–Sat · 9am–1pm &amp; 4pm–9pm</p>
                </div>
            </div>
        </div>

        <div class="mt-14 flex flex-col gap-4 border-t border-white/10 pt-8 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-white/40">&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
            <ul class="flex gap-5 text-xs uppercase tracking-[0.15em] text-white/60">
                @foreach ($social as $item)
                    <li><a href="{{ $item['url'] }}" class="hover:text-accent">{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>
