@php($rtl = app()->getLocale() === 'ar')
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }}</title>
</head>
<body style="margin:0; font-family: Arial, Helvetica, sans-serif; color:#16130f; background:#f6f2ec; padding:24px; text-align:{{ $rtl ? 'right' : 'left' }};">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; padding:32px;">
        <p style="font-size:12px; letter-spacing:3px; text-transform:uppercase; color:#8a6d4b; margin:0;">{{ config('app.name') }}</p>
        <h1 style="font-size:22px; margin:12px 0 8px;">{{ __('You left something behind') }}</h1>
        <p style="color:#555; margin:0 0 16px;">{{ __('Your selections are still saved. Complete your order before they sell out.') }}</p>

        <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:14px;">
            @foreach ($cart->items as $item)
                <tr>
                    <td style="padding:8px 0; border-bottom:1px solid #eee;">
                        {{ $item->variant?->product?->name ?? __('Item') }}@if ($item->variant?->label) ({{ $item->variant->label }})@endif &times; {{ $item->quantity }}
                    </td>
                    <td style="padding:8px 0; border-bottom:1px solid #eee; text-align:{{ $rtl ? 'left' : 'right' }};">{{ format_omr($item->lineTotalBaisa()) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="font-weight:bold; padding-top:10px;">{{ __('Subtotal') }}</td>
                <td style="font-weight:bold; padding-top:10px; text-align:{{ $rtl ? 'left' : 'right' }};">{{ format_omr($cart->subtotalBaisa()) }}</td>
            </tr>
        </table>

        <p style="margin:28px 0;">
            <a href="{{ route('cart.index') }}"
                style="display:inline-block; background:#16130f; color:#ffffff; text-decoration:none; padding:14px 28px; font-size:12px; letter-spacing:2px; text-transform:uppercase;">
                {{ __('Complete your purchase') }}
            </a>
        </p>

        <p style="color:#999; font-size:12px;">{{ __('Sign in to see your saved bag.') }}</p>
    </div>
</body>
</html>
