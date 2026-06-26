<x-layouts.storefront title="{{ __('Reset Password') }}" :noindex="true">
    <section class="mx-auto max-w-md px-4 sm:px-6 py-16">
        <h1 class="mb-8 text-center text-3xl text-ink">{{ __('Choose a new password') }}</h1>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-storefront.field name="email" label="{{ __('Email') }}" type="email" :value="old('email', $email)" />
            <x-storefront.field name="password" label="{{ __('New password') }}" type="password" />
            <x-storefront.field name="password_confirmation" label="{{ __('Confirm new password') }}" type="password" />

            <button type="submit" class="w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                {{ __('Reset password') }}
            </button>
        </form>
    </section>
</x-layouts.storefront>
