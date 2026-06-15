<x-layouts.storefront title="Create Account">
    <section class="mx-auto max-w-md px-4 sm:px-6 py-16">
        <h1 class="mb-8 text-center text-3xl text-ink">Create Account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            <x-storefront.field name="name" label="Full name" :value="old('name')" />
            <x-storefront.field name="email" label="Email" type="email" :value="old('email')" />
            <x-storefront.field name="phone" label="Phone (optional)" :value="old('phone')" :required="false" />
            <x-storefront.field name="password" label="Password" type="password" />
            <x-storefront.field name="password_confirmation" label="Confirm password" type="password" :required="true" />

            <button type="submit" class="w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                Create Account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            Already have an account? <a href="{{ route('login') }}" class="text-accent hover:underline">Sign in</a>
        </p>
    </section>
</x-layouts.storefront>
