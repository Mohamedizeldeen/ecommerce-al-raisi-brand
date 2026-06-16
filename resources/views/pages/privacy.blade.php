<x-layouts.storefront title="{{ __('Privacy Policy') }}">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
        <h1 class="text-4xl text-ink">{{ __('Privacy Policy') }}</h1>
        <div class="mt-8 space-y-5 text-sm leading-relaxed text-stone-600">
            <p>{{ __('This Privacy Policy describes how') }} {{ config('app.name') }} {{ __('collects, uses and protects the information you provide when you use our website and place an order.') }}</p>
            <h2 class="pt-2 text-base text-ink">{{ __('Information We Collect') }}</h2>
            <p>{{ __('We collect the details you provide at checkout and when contacting us — your name, email, phone number and shipping address — together with order information necessary to fulfil your purchase.') }}</p>
            <h2 class="pt-2 text-base text-ink">{{ __('How We Use Your Information') }}</h2>
            <p>{{ __('Your information is used to process orders, arrange delivery, respond to enquiries and, where you have opted in, to send you news about new collections. We never sell your personal data.') }}</p>
            <h2 class="pt-2 text-base text-ink">{{ __('Payments') }}</h2>
            <p>{{ __('Payments are processed securely by our payment provider. We do not store your card details on our servers.') }}</p>
            <h2 class="pt-2 text-base text-ink">{{ __('Contact') }}</h2>
            <p>{{ __('For any privacy questions, please') }} <a href="{{ route('contact') }}" class="text-accent hover:underline">{{ __('contact us') }}</a>.</p>
        </div>
    </section>
</x-layouts.storefront>
