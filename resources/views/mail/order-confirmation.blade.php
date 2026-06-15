<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Order {{ $order->order_number }}</title>
</head>
<body style="margin:0; font-family: Arial, Helvetica, sans-serif; color:#16130f; background:#f6f2ec; padding:24px;">
    <div style="max-width:560px; margin:0 auto; background:#ffffff; padding:32px;">
        <p style="font-size:12px; letter-spacing:3px; text-transform:uppercase; color:#8a6d4b; margin:0;">{{ config('app.name') }}</p>
        <h1 style="font-size:22px; margin:12px 0 4px;">Thank you for your order</h1>
        <p style="color:#555;">Hi {{ $order->customer_name }}, we have received your payment for order
            <strong>{{ $order->order_number }}</strong>.</p>

        <table style="width:100%; border-collapse:collapse; margin-top:16px; font-size:14px;">
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding:8px 0; border-bottom:1px solid #eee;">
                        {{ $item->name }}@if ($item->variant_label) ({{ $item->variant_label }})@endif &times; {{ $item->quantity }}
                    </td>
                    <td style="padding:8px 0; border-bottom:1px solid #eee; text-align:right;">{{ format_omr($item->line_total_baisa) }}</td>
                </tr>
            @endforeach
            <tr>
                <td style="padding-top:12px; color:#555;">Subtotal</td>
                <td style="padding-top:12px; text-align:right;">{{ format_omr($order->subtotal_baisa) }}</td>
            </tr>
            @if ($order->discount_baisa > 0)
                <tr>
                    <td style="color:#8a6d4b;">Discount</td>
                    <td style="text-align:right; color:#8a6d4b;">-{{ format_omr($order->discount_baisa) }}</td>
                </tr>
            @endif
            <tr>
                <td style="color:#555;">Shipping</td>
                <td style="text-align:right;">{{ $order->shipping_baisa > 0 ? format_omr($order->shipping_baisa) : 'Free' }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; padding-top:8px;">Total</td>
                <td style="font-weight:bold; text-align:right; padding-top:8px;">{{ format_omr($order->total_baisa) }}</td>
            </tr>
        </table>

        <p style="margin-top:24px; color:#777; font-size:14px;">
            We will be in touch when your order ships to {{ $order->shipping_city }}, {{ $order->shipping_country }}.
        </p>
    </div>
</body>
</html>
