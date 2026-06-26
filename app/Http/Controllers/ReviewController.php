<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        // Only reviewable while the product is publicly visible.
        abort_unless(
            $product->is_active && ($product->published_at === null || $product->published_at <= now()),
            404
        );

        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:120'],
            'author_email' => ['required', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        // "Verified purchase" when a paid order for this product matches the reviewer
        // (by account or by the email they entered).
        $verified = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->where(function ($q) use ($data) {
                $q->where('customer_email', $data['author_email']);
                if (Auth::check()) {
                    $q->orWhere('user_id', Auth::id());
                }
            })
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        $product->reviews()->create([
            'user_id' => Auth::id(),
            'author_name' => $data['author_name'],
            'author_email' => $data['author_email'],
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
            'is_verified_purchase' => $verified,
            'is_approved' => false, // held for moderation
        ]);

        return back()->with('success', __('Thank you! Your review will appear once it has been approved.'));
    }
}
