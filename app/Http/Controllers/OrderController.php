<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
}
