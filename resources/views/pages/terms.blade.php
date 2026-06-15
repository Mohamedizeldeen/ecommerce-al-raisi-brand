<x-layouts.storefront title="Terms of Service">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
        <h1 class="text-4xl text-ink">Terms of Service</h1>
        <div class="mt-8 space-y-5 text-sm leading-relaxed text-stone-600">
            <p>By accessing and using this website you agree to the following terms. Please read them carefully.</p>
            <h2 class="pt-2 text-base text-ink">Orders</h2>
            <p>All orders are subject to availability and acceptance. Prices are shown in Omani Rial (OMR) and
                include applicable taxes unless stated otherwise.</p>
            <h2 class="pt-2 text-base text-ink">Intellectual Property</h2>
            <p>All content on this site — including imagery, designs and text — is the property of
                {{ config('app.name') }} and may not be reproduced without permission.</p>
            <h2 class="pt-2 text-base text-ink">Liability</h2>
            <p>We make every effort to display our products accurately, but cannot guarantee that your device
                displays colours faithfully.</p>
            <h2 class="pt-2 text-base text-ink">Contact</h2>
            <p>Questions about these terms? <a href="{{ route('contact') }}" class="text-accent hover:underline">Contact us</a>.</p>
        </div>
    </section>
</x-layouts.storefront>
