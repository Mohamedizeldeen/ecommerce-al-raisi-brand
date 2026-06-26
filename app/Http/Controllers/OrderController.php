<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        abort_unless(
            session('recent_order') === $order->order_number
                || (Auth::check() && Auth::id() === $order->user_id),
            403
        );

        $order->load('items');

        return view('orders.show', compact('order'));
    }

    /** Guest order-lookup form (order number + email). */
    public function lookupForm()
    {
        return view('orders.lookup');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $order = Order::where('order_number', $data['order_number'])->first();

        // Constant-time email comparison, identical generic error whether or not the
        // order number exists — never reveal which order numbers are valid.
        if (! $order || ! hash_equals(strtolower($order->customer_email), strtolower($data['email']))) {
            return back()->withInput($request->only('order_number'))
                ->withErrors(['order_number' => __('We could not find an order matching those details.')]);
        }

        // Grant access via the same session gate that show() enforces.
        session(['recent_order' => $order->order_number]);

        return redirect()->route('orders.show', $order);
    }
}
