<x-layouts.storefront title="Privacy Policy">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
        <h1 class="text-4xl text-ink">Privacy Policy</h1>
        <div class="mt-8 space-y-5 text-sm leading-relaxed text-stone-600">
            <p>This Privacy Policy describes how {{ config('app.name') }} collects, uses and protects the
                information you provide when you use our website and place an order.</p>
            <h2 class="pt-2 text-base text-ink">Information We Collect</h2>
            <p>We collect the details you provide at checkout and when contacting us — your name, email, phone
                number and shipping address — together with order information necessary to fulfil your purchase.</p>
            <h2 class="pt-2 text-base text-ink">How We Use Your Information</h2>
            <p>Your information is used to process orders, arrange delivery, respond to enquiries and, where you
                have opted in, to send you news about new collections. We never sell your personal data.</p>
            <h2 class="pt-2 text-base text-ink">Payments</h2>
            <p>Payments are processed securely by our payment provider. We do not store your card details on our
                servers.</p>
            <h2 class="pt-2 text-base text-ink">Contact</h2>
            <p>For any privacy questions, please <a href="{{ route('contact') }}" class="text-accent hover:underline">contact us</a>.</p>
        </div>
    </section>
</x-layouts.storefront>
