<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index()
    {
        // Self-heal the bag against live stock (drop sold-out items, clamp quantities).
        // On a change, redirect so the standard flash shows and the view re-renders clean.
        if ($this->cart->reconcile()) {
            return redirect()->route('cart.index')
                ->with('error', __('Your bag was updated to reflect current availability — please review it before checking out.'));
        }

        $cart = $this->cart->current();
        $cart->load(['items.variant.product.media']);

        return view('cart.index', [
            'items' => $cart->items,
            'cart' => $cart,
            'summary' => $this->cart->summary(),
        ]);
    }

    /**
     * Render the slide-out mini-cart contents (AJAX, JSON-wrapped HTML).
     */
    public function drawer()
    {
        $this->cart->reconcile();

        $cart = $this->cart->current();
        $cart->load(['items.variant.product.media']);

        $html = view('partials.cart-drawer', [
            'items' => $cart->items,
            'summary' => $this->cart->summary(),
        ])->render();

        return response()->json(['html' => $html, 'count' => $this->cart->count()]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $variant = ProductVariant::findOrFail($data['variant_id']);

        if (! $variant->is_active || $variant->stock_qty < 1) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => 'Sorry, that item is out of stock.'], 422)
                : back()->with('error', 'Sorry, that item is currently out of stock.');
        }

        $this->cart->add($variant, $data['quantity'] ?? 1);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'count' => $this->cart->count(),
                'message' => 'Added to your bag.',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Added to your bag.');
    }

    public function update(Request $request, CartItem $item)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:20'],
        ]);

        $this->cart->updateItem($item, $data['quantity']);

        return back()->with('success', 'Bag updated.');
    }

    public function remove(CartItem $item)
    {
        $this->cart->removeItem($item);

        return back()->with('success', 'Item removed.');
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        if (empty($data['code'])) {
            $this->cart->clearCoupon();

            return back()->with('success', 'Promo code removed.');
        }

        return $this->cart->applyCoupon($data['code'])
            ? back()->with('success', 'Promo code applied.')
            : back()->with('error', 'That promo code is not valid.');
    }
}
