<?php

namespace App\Http\Controllers;

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\StockUnavailableException;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\Payments\ThawaniService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly ThawaniService $thawani,
        private readonly MarkOrderPaid $markOrderPaid,
    ) {}

    public function index()
    {
        $cart = $this->cart->current();
        $cart->load(['items.variant.product']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        return view('checkout.index', [
            'cart' => $cart,
            'summary' => $this->cart->summary(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'shipping_address_line1' => ['required', 'string', 'max:255'],
            'shipping_address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_region' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = $this->cart->current();
        $cart->load(['items.variant.product']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your bag is empty.');
        }

        $summary = $this->cart->summary();

        try {
            $order = DB::transaction(function () use ($cart, $summary, $data) {
                // Re-validate availability under a row lock so we never create (and charge
                // for) an order whose items went out of stock between cart and checkout.
                $variantIds = $cart->items->pluck('product_variant_id')->all();
                $locked = ProductVariant::whereIn('id', $variantIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($cart->items as $item) {
                    $variant = $locked->get($item->product_variant_id);
                    if (! $variant || ! $variant->is_active || $variant->stock_qty < $item->quantity) {
                        throw new StockUnavailableException((string) $item->variant?->product?->name);
                    }
                }

                $order = Order::create([
                    'order_number' => Order::generateNumber(),
                    'user_id' => Auth::id(),
                    'status' => OrderStatus::Pending,
                    'payment_status' => PaymentStatus::Pending,
                    'subtotal_baisa' => $summary['subtotal'],
                    'shipping_baisa' => $summary['shipping'],
                    'discount_baisa' => $summary['discount'],
                    'total_baisa' => $summary['total'],
                    'currency' => 'OMR',
                    'coupon_code' => $cart->coupon_code,
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'],
                    'customer_phone' => $data['customer_phone'],
                    'shipping_address_line1' => $data['shipping_address_line1'],
                    'shipping_address_line2' => $data['shipping_address_line2'] ?? null,
                    'shipping_city' => $data['shipping_city'],
                    'shipping_region' => $data['shipping_region'] ?? null,
                    'shipping_country' => 'Oman',
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($cart->items as $item) {
                    $variant = $item->variant;
                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'product_id' => $variant->product_id,
                        'name' => $variant->product->name,
                        'variant_label' => $variant->label,
                        'sku' => $variant->sku,
                        'price_baisa' => $variant->price_baisa,
                        'quantity' => $item->quantity,
                        'line_total_baisa' => $item->lineTotalBaisa(),
                    ]);
                }

                $order->statusHistories()->create([
                    'to_status' => OrderStatus::Pending->value,
                    'note' => 'Order created at checkout.',
                ]);

                return $order;
            });
        } catch (StockUnavailableException $e) {
            return redirect()->route('cart.index')
                ->with('error', __('Sorry, an item in your bag just sold out. Please review your bag before paying.'));
        }

        session(['recent_order' => $order->order_number]);

        try {
            $order->load('items');
            $redirectUrl = $this->thawani->createSession($order);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('cart.index')
                ->with('error', 'We could not start the payment. Please try again in a moment.');
        }

        return redirect()->away($redirectUrl);
    }

    public function success(Request $request)
    {
        $order = Order::where('order_number', (string) $request->query('ref'))->firstOrFail();

        // Only the buyer who just checked out (session set in store()) or the order's
        // owner may land here. Never grant access from the raw `ref` query param alone —
        // doing so was an IDOR that exposed any order's customer PII.
        abort_unless(
            session('recent_order') === $order->order_number
                || (Auth::check() && Auth::id() === $order->user_id),
            403
        );

        try {
            if ($order->thawani_session_id) {
                $session = $this->thawani->retrieveSession($order->thawani_session_id);

                if ($this->thawani->isPaid($session, (int) $order->total_baisa)) {
                    $this->markOrderPaid->handle($order);
                    $this->cart->clear();
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('orders.show', $order->fresh());
    }

    public function cancel(Request $request)
    {
        // If the buyer abandoned an order that is still pending/unpaid, mark it
        // cancelled so it doesn't linger as a stale pending order.
        if ($recent = session('recent_order')) {
            $order = Order::where('order_number', $recent)->first();

            if ($order
                && $order->status === OrderStatus::Pending
                && $order->payment_status !== PaymentStatus::Paid) {
                $from = $order->payment_status->value;

                $order->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => PaymentStatus::Cancelled,
                ]);

                $order->statusHistories()->create([
                    'from_status' => $from,
                    'to_status' => OrderStatus::Cancelled->value,
                    'note' => 'Payment cancelled by customer at checkout.',
                ]);
            }
        }

        return redirect()->route('cart.index')
            ->with('error', 'Payment was cancelled — your bag has been saved.');
    }
}
