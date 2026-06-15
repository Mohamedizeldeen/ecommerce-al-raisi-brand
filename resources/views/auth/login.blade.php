<x-layouts.storefront title="Sign In">
    <section class="mx-auto max-w-md px-4 sm:px-6 py-16">
        <h1 class="mb-8 text-center text-3xl text-ink">Sign In</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <x-storefront.field name="email" label="Email" type="email" :value="old('email')" />
            <x-storefront.field name="password" label="Password" type="password" />

            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="border-stone-soft"> Remember me
            </label>

            <button type="submit" class="w-full bg-ink py-3.5 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">
                Sign In
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-500">
            New here? <a href="{{ route('register') }}" class="text-accent hover:underline">Create an account</a>
        </p>
    </section>
</x-layouts.storefront>
