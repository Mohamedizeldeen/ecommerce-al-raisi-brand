<x-layouts.storefront title="{{ __('Reset Password') }}" :noindex="true">
    <section class="mx-auto max-w-md px-4 sm:px-6 py-16">
        <h1 class="mb-3 text-center text-3xl text-ink">{{ __('Reset your password') }}</h1>
        <p class="mb-8 text-center text-sm text-stone-500">{{ __('Enter your email and we will send you a link to reset your password.') }}</p>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <x-storefront.field name="email" label="{{ __('Email') }}" type="email" :value="old('email')" />

            <button type="submit" class="w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                {{ __('Send reset link') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            <a href="{{ route('login') }}" class="text-accent hover:underline">{{ __('Back to sign in') }}</a>
        </p>
    </section>
</x-layouts.storefront>
