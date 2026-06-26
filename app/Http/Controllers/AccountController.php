<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $recentOrders = $user->orders()->latest()->take(5)->get();

        return view('account.dashboard', compact('user', 'recentOrders'));
    }

    public function orders()
    {
        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('items', 'statusHistories');

        return view('account.order', compact('order'));
    }

    /** Re-add a past order's still-available items to the current cart ("buy again"). */
    public function reorder(Order $order, CartService $cart)
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $order->load('items.variant.product');
        $added = 0;
        $skipped = 0;

        foreach ($order->items as $item) {
            $variant = $item->variant;
            $product = $variant?->product;
            $available = $variant && $variant->is_active && $variant->stock_qty > 0
                && $product && $product->is_active
                && ($product->published_at === null || $product->published_at <= now());

            if ($available) {
                $cart->add($variant, min((int) $item->quantity, (int) $variant->stock_qty));
                $added++;
            } else {
                $skipped++;
            }
        }

        if ($added === 0) {
            return redirect()->route('account.orders.show', $order)
                ->with('error', __('None of the items from that order are available right now.'));
        }

        $message = __('Items from your order have been added to your bag.');
        if ($skipped > 0) {
            $message .= ' '.__(':count item(s) were unavailable and skipped.', ['count' => $skipped]);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
}
