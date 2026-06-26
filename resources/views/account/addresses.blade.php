<x-layouts.storefront :title="__('My Addresses')" :noindex="true">
    <section class="mx-auto max-w-3xl px-4 sm:px-6 py-12">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl text-ink">{{ __('My Addresses') }}</h1>
            <a href="{{ route('account.dashboard') }}" class="text-xs uppercase tracking-[0.15em] text-accent hover:underline">{{ __('My Account') }}</a>
        </div>

        @if ($addresses->isNotEmpty())
            <div class="space-y-4">
                @foreach ($addresses as $address)
                    <div class="flex items-start justify-between gap-4 border border-stone-soft p-5">
                        <div class="text-sm text-stone-600">
                            <p class="text-ink">{{ $address->name }}
                                @if ($address->is_default)
                                    <span class="ms-2 rounded-full bg-sand px-2 py-0.5 text-[10px] uppercase tracking-[0.15em] text-ink">{{ __('Default') }}</span>
                                @endif
                            </p>
                            <p>{{ $address->line1 }}</p>
                            @if ($address->line2)<p>{{ $address->line2 }}</p>@endif
                            <p>{{ collect([$address->city, $address->region, $address->country])->filter()->implode(', ') }}</p>
                            @if ($address->phone)<p>{{ $address->phone }}</p>@endif
                        </div>
                        <div class="flex flex-col items-end gap-2 text-xs">
                            @unless ($address->is_default)
                                <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                                    @csrf
                                    <button type="submit" class="uppercase tracking-[0.12em] text-accent hover:underline">{{ __('Make default') }}</button>
                                </form>
                            @endunless
                            <form method="POST" action="{{ route('account.addresses.destroy', $address) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="uppercase tracking-[0.12em] text-stone-400 hover:text-red-600">{{ __('Remove') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-stone-500">{{ __('You have no saved addresses yet.') }}</p>
        @endif

        <h2 class="mb-4 mt-12 text-lg text-ink">{{ __('Add a new address') }}</h2>
        <form method="POST" action="{{ route('account.addresses.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <x-storefront.field name="name" label="{{ __('Full name') }}" :value="old('name', auth()->user()->name)" />
            <x-storefront.field name="phone" label="{{ __('Phone') }}" :value="old('phone', auth()->user()->phone)" :required="false" />
            <div class="sm:col-span-2"><x-storefront.field name="line1" label="{{ __('Address line 1') }}" :value="old('line1')" /></div>
            <div class="sm:col-span-2"><x-storefront.field name="line2" label="{{ __('Address line 2 (optional)') }}" :value="old('line2')" :required="false" /></div>
            <x-storefront.field name="city" label="{{ __('City') }}" :value="old('city')" />
            <x-storefront.field name="region" label="{{ __('Region (optional)') }}" :value="old('region')" :required="false" />
            <label class="flex items-center gap-2 text-sm text-stone-600 sm:col-span-2">
                <input type="checkbox" name="is_default" value="1" class="border-stone-soft"> {{ __('Set as default address') }}
            </label>
            <div class="sm:col-span-2">
                <button type="submit" class="bg-ink px-6 py-3 text-xs uppercase tracking-[0.2em] text-white transition hover:bg-accent">{{ __('Save address') }}</button>
            </div>
        </form>
    </section>
</x-layouts.storefront>
