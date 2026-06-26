<x-layouts.storefront title="{{ __('Size Guide') }}" :description="__('Find your perfect fit with the Amal Al Raisi size guide for abayas, kaftans and occasion wear.')">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-16">
        <h1 class="text-center text-4xl text-ink">{{ __('Size Guide') }}</h1>
        <p class="mt-3 text-center text-stone-500">{{ __('All measurements are in centimetres.') }}</p>

        <div class="mt-10 overflow-x-auto">
            <table class="w-full border border-stone-soft text-sm">
                <thead class="bg-sand text-left uppercase tracking-[0.12em] text-ink">
                    <tr>
                        <th class="px-4 py-3">{{ __('Size') }}</th>
                        <th class="px-4 py-3">{{ __('Bust') }}</th>
                        <th class="px-4 py-3">{{ __('Waist') }}</th>
                        <th class="px-4 py-3">{{ __('Hips') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-soft text-stone-600">
                    <tr><td class="px-4 py-3">S</td><td class="px-4 py-3">82–86</td><td class="px-4 py-3">64–68</td><td class="px-4 py-3">90–94</td></tr>
                    <tr><td class="px-4 py-3">M</td><td class="px-4 py-3">87–91</td><td class="px-4 py-3">69–73</td><td class="px-4 py-3">95–99</td></tr>
                    <tr><td class="px-4 py-3">L</td><td class="px-4 py-3">92–97</td><td class="px-4 py-3">74–79</td><td class="px-4 py-3">100–105</td></tr>
                    <tr><td class="px-4 py-3">XL</td><td class="px-4 py-3">98–104</td><td class="px-4 py-3">80–86</td><td class="px-4 py-3">106–112</td></tr>
                </tbody>
            </table>
        </div>

        <p class="mt-6 text-sm text-stone-500">{{ __('For help choosing your size, please') }} <a href="{{ route('contact') }}" class="text-accent hover:underline">{{ __('contact us') }}</a> {{ __('— we are always happy to advise.') }}</p>
    </section>
</x-layouts.storefront>
