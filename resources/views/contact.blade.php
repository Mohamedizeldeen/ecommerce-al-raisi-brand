<x-layouts.storefront title="{{ __('Contact') }}">
    <section class="mx-auto max-w-5xl px-4 sm:px-6 py-12">
        <h1 class="mb-3 text-center text-4xl text-ink">{{ __('Contact Us') }}</h1>
        <p class="mb-10 text-center text-stone-500">{{ __('We would love to hear from you.') }}</p>

        <div class="grid gap-12 lg:grid-cols-2">
            <form method="POST" action="{{ route('contact') }}" class="space-y-5">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                <x-storefront.field name="name" label="{{ __('Name') }}" :value="old('name')" />
                <x-storefront.field name="email" label="{{ __('Email') }}" type="email" :value="old('email')" />
                <x-storefront.field name="phone" label="{{ __('Phone (optional)') }}" :value="old('phone')" :required="false" />

                <div>
                    <label for="message" class="mb-1 block text-xs uppercase tracking-[0.15em] text-ink">{{ __('Message') }}</label>
                    <textarea id="message" name="message" rows="5" required
                        class="w-full border px-3 py-2 text-sm focus:outline-none {{ $errors->has('message') ? 'border-red-400' : 'border-stone-soft focus:border-accent' }}">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                @if (config('services.hcaptcha.sitekey'))
                    <div class="h-captcha" data-sitekey="{{ config('services.hcaptcha.sitekey') }}"></div>
                @endif

                <button type="submit" class="bg-ink px-8 py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                    {{ __('Send Message') }}
                </button>
            </form>

            <div class="space-y-5 text-sm text-stone-600">
                <div>
                    <h2 class="mb-1 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Showroom') }}</h2>
                    <p>{{ \App\Models\Setting::get('address_line', 'Al Athaiba, Muscat, Sultanate of Oman') }}</p>
                </div>
                <div>
                    <h2 class="mb-1 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Opening Hours') }}</h2>
                    <p>{{ __('Thursday – Saturday · 9am–1pm') }} &amp; {{ __('4pm–9pm') }}</p>
                </div>
                <div>
                    <h2 class="mb-1 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Email') }}</h2>
                    <p>{{ \App\Models\Setting::get('contact_email', 'hello@amalalraisi.com') }}</p>
                </div>
                <div>
                    <h2 class="mb-1 text-xs uppercase tracking-[0.18em] text-ink">{{ __('Phone') }}</h2>
                    <p>{{ \App\Models\Setting::get('contact_phone', '+968 2400 0000') }}</p>
                </div>
            </div>
        </div>
    </section>

    @if (config('services.hcaptcha.sitekey'))
        @push('scripts')
            <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
        @endpush
    @endif
</x-layouts.storefront>
