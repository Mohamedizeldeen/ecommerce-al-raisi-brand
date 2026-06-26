<?php

namespace App\Http\Controllers;

use App\Actions\Orders\MarkOrderPaid;
use App\Actions\Orders\ReleaseOrderStock;
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
        if ($this->cart->reconcile()) {
            return redirect()->route('cart.index')
                ->with('error', __('Your bag was updated to reflect current availability — please review it before checking out.'));
        }

        $cart = $this->cart->current();
        $cart->load(['items.variant.product']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your bag is empty.'));
        }

        return view('checkout.index', [
            'cart' => $cart,
            'summary' => $this->cart->summary(),
            'defaultAddress' => Auth::user()?->defaultAddress(),
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

        // Self-heal against live stock before charging. If the bag changed (an item
        // sold out, or a quantity was clamped) send the customer back to review it
        // rather than charging for something they didn't confirm — this also breaks
        // the old deadlock where a sold-out item neither paid nor was ever removed.
        if ($this->cart->reconcile()) {
            return redirect()->route('cart.index')
                ->with('error', __('Your bag was updated to reflect current availability — please review it before checking out.'));
        }

        $cart = $this->cart->current();
        $cart->load(['items.variant.product']);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your bag is empty.'));
        }

        // Optionally remember this address for the signed-in customer.
        if (Auth::check() && $request->boolean('save_address')) {
            $this->saveCheckoutAddress($data);
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
                    $product = $variant?->product;
                    $productAvailable = $product && $product->is_active
                        && ($product->published_at === null || $product->published_at <= now());

                    if (! $variant || ! $variant->is_active || ! $productAvailable || $variant->stock_qty < $item->quantity) {
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
                    'tax_baisa' => $summary['tax'],
                    'vat_percent' => $summary['vat_percent'],
                    'total_baisa' => $summary['total'],
                    'currency' => 'OMR',
                    'locale' => app()->getLocale(),
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

                // Reserve stock now (the variants are locked) so the payment window
                // holds the units — two shoppers can't both pay for the last piece.
                // ReleaseOrderStock returns these on cancel/expiry/failure.
                foreach ($cart->items as $item) {
                    $locked->get($item->product_variant_id)->decrement('stock_qty', (int) $item->quantity);
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

            // The order exists with reserved stock but payment never started — release
            // the reservation now rather than holding the units until the 24h expiry.
            app(ReleaseOrderStock::class)->handle(
                $order, OrderStatus::Cancelled, PaymentStatus::Failed,
                'Payment could not be started — reservation released.'
            );

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
                app(ReleaseOrderStock::class)->handle(
                    $order, OrderStatus::Cancelled, PaymentStatus::Cancelled,
                    'Payment cancelled by customer at checkout — reservation released.'
                );
            }
        }

        return redirect()->route('cart.index')
            ->with('error', 'Payment was cancelled — your bag has been saved.');
    }

    /** Persist the checkout shipping details into the customer's address book. */
    private function saveCheckoutAddress(array $data): void
    {
        $user = Auth::user();

        $address = $user->addresses()->create([
            'name' => $data['customer_name'],
            'phone' => $data['customer_phone'] ?? null,
            'line1' => $data['shipping_address_line1'],
            'line2' => $data['shipping_address_line2'] ?? null,
            'city' => $data['shipping_city'],
            'region' => $data['shipping_region'] ?? null,
            'country' => 'Oman',
        ]);

        if ($user->addresses()->count() === 1) {
            $address->update(['is_default' => true]);
        }
    }
}
