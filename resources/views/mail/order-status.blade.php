@php($rtl = app()->getLocale() === 'ar')
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $order->order_number }}</title>
</head>
<body style="margin:0; font-family: Arial, Helvetica, sans-serif; color:#16130f; background:#f6f2ec; padding:24px; text-align:{{ $rtl ? 'right' : 'left' }};">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; padding:32px;">
        <p style="font-size:12px; letter-spacing:3px; text-transform:uppercase; color:#8a6d4b; margin:0;">{{ config('app.name') }}</p>
        <h1 style="font-size:22px; margin:12px 0 8px;">{{ __($headline) }}</h1>
        <p style="color:#555; margin:0 0 8px;">{{ __('Hi :name,', ['name' => $order->customer_name]) }}</p>
        <p style="color:#555; margin:0 0 16px;">{{ __($body) }}</p>
        <p style="margin:0 0 16px;"><strong>{{ __('Order') }} {{ $order->order_number }}</strong></p>

        @if ($showTracking && $order->tracking_number)
            <div style="border:1px solid #eee; padding:16px; margin:0 0 16px;">
                <p style="margin:0; font-size:12px; text-transform:uppercase; letter-spacing:2px; color:#8a6d4b;">{{ __('Tracking') }}</p>
                <p style="margin:6px 0 0; font-size:15px;">
                    @if ($order->carrier){{ $order->carrier }} — @endif<strong>{{ $order->tracking_number }}</strong>
                </p>
            </div>
        @endif

        <p style="margin-top:24px; color:#777; font-size:14px;">{{ __('Thank you for shopping with :name.', ['name' => config('app.name')]) }}</p>
    </div>
</body>
</html>
