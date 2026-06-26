@php($rtl = app()->getLocale() === 'ar')
@php($product = $variant->product)
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; font-family: Arial, Helvetica, sans-serif; color:#16130f; background:#f6f2ec; padding:24px; text-align:{{ $rtl ? 'right' : 'left' }};">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; padding:32px;">
        <p style="font-size:12px; letter-spacing:3px; text-transform:uppercase; color:#8a6d4b; margin:0;">{{ config('app.name') }}</p>
        <h1 style="font-size:22px; margin:12px 0 8px;">{{ __('It is back in stock') }}</h1>
        <p style="color:#555; margin:0 0 16px;">
            {{ __('Good news — :item is available again.', ['item' => $product?->name]) }}
            @if ($variant->label && $variant->label !== 'Default')<br><span style="color:#8a6d4b;">{{ $variant->label }}</span>@endif
        </p>

        <p style="margin:24px 0;">
            <a href="{{ $product ? route('products.show', $product) : url('/') }}"
                style="display:inline-block; background:#16130f; color:#ffffff; text-decoration:none; padding:14px 28px; font-size:12px; letter-spacing:2px; text-transform:uppercase;">
                {{ __('Shop now') }}
            </a>
        </p>

        <p style="color:#999; font-size:12px;">{{ __('Hurry — popular pieces sell out fast.') }}</p>
    </div>
</body>
</html>
