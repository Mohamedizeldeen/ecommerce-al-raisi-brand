<x-layouts.storefront title="{{ __('Sign In') }}">
    <section class="mx-auto max-w-md px-4 sm:px-6 py-16">
        <h1 class="mb-8 text-center text-3xl text-ink">{{ __('Sign In') }}</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <x-storefront.field name="email" label="{{ __('Email') }}" type="email" :value="old('email')" />
            <x-storefront.field name="password" label="{{ __('Password') }}" type="password" />

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 text-stone-600">
                    <input type="checkbox" name="remember" class="border-stone-soft"> {{ __('Remember me') }}
                </label>
                <a href="{{ route('password.request') }}" class="text-accent hover:underline">{{ __('Forgot password?') }}</a>
            </div>

            <button type="submit" class="w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                {{ __('Sign In') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            {{ __('New here?') }} <a href="{{ route('register') }}" class="text-accent hover:underline">{{ __('Create an account') }}</a>
        </p>
        <p class="mt-2 text-center text-sm text-stone-500">
            <a href="{{ route('orders.lookup') }}" class="text-accent hover:underline">{{ __('Track a guest order') }}</a>
        </p>
    </section>
</x-layouts.storefront>
