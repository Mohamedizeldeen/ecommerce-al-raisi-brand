@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'required' => true,
])

<div>
    <label for="{{ $name }}" class="mb-1 block text-xs uppercase tracking-[0.15em] text-ink">{{ $label }}</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}" @required($required)
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        class="w-full border px-3 py-2 text-sm focus:outline-none {{ $errors->has($name) ? 'border-red-400 focus:border-red-500' : 'border-stone-soft focus:border-accent' }}">
    @error($name)
        <p id="{{ $name }}-error" class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
