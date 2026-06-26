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
        <h1 style="font-size:22px; margin:12px 0 4px;">{{ __('Thank you for your order') }}</h1>
        <p style="color:#555;">{{ __('Hi :name, we have received your payment for order :number.', ['name' => $order->customer_name, 'number' => $order->order_number]) }}</p>

        <table style="width:100%; border-collapse:collapse; margin-top:16px; font-size:14px;">
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding:8px 0; border-bottom:1px solid #eee;">
                        {{ $item->name }}@if ($item->variant_label) ({{ $item->variant_label }})@endif &times; {{ $item->quantity }}
                    </td>
                    <td style="padding:8px 0; border-bottom:1px solid #eee; text-align:{{ $rtl ? 'left' : 'right' }};">{{ format_omr($item->line_total_baisa) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="padding-top:12px; color:#555;">{{ __('Subtotal') }}</td>
                <td style="padding-top:12px; text-align:{{ $rtl ? 'left' : 'right' }};">{{ format_omr($order->subtotal_baisa) }}</td>
            </tr>
            @if ($order->discount_baisa > 0)
                <tr>
                    <td style="color:#8a6d4b;">{{ __('Discount') }}</td>
                    <td style="text-align:{{ $rtl ? 'left' : 'right' }}; color:#8a6d4b;">-{{ format_omr($order->discount_baisa) }}</td>
                </tr>
            @endif
            <tr>
                <td style="color:#555;">{{ __('Shipping') }}</td>
                <td style="text-align:{{ $rtl ? 'left' : 'right' }};">{{ $order->shipping_baisa > 0 ? format_omr($order->shipping_baisa) : __('Free') }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; padding-top:8px;">{{ __('Total') }}</td>
                <td style="font-weight:bold; text-align:{{ $rtl ? 'left' : 'right' }}; padding-top:8px;">{{ format_omr($order->total_baisa) }}</td>
            </tr>
            @if ($order->tax_baisa > 0)
                <tr>
                    <td style="color:#999; font-size:12px;">{{ __('Includes VAT (:percent%)', ['percent' => $order->vat_percent]) }}</td>
                    <td style="color:#999; font-size:12px; text-align:{{ $rtl ? 'left' : 'right' }};">{{ format_omr($order->tax_baisa) }}</td>
                </tr>
            @endif
        </table>

        <p style="margin-top:24px; color:#777; font-size:14px;">
            {{ __('We will be in touch when your order ships to :city, :country.', ['city' => $order->shipping_city, 'country' => $order->shipping_country]) }}
        </p>
    </div>
</body>
</html>
