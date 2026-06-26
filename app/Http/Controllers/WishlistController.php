<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $products = Auth::user()->wishlistProducts()
            ->with(['media', 'variants'])
            ->orderByPivot('created_at', 'desc')
            ->get();

        return view('account.wishlist', compact('products'));
    }

    public function toggle(Product $product)
    {
        $user = Auth::user();

        if ($user->wishlistProducts()->whereKey($product->id)->exists()) {
            $user->wishlistProducts()->detach($product->id);

            return back()->with('success', __('Removed from your wishlist.'));
        }

        $user->wishlistProducts()->syncWithoutDetaching([$product->id]);

        return back()->with('success', __('Saved to your wishlist.'));
    }
}
