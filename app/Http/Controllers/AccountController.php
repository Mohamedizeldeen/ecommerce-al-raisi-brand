<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
}
