<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockNotification;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $variant = ProductVariant::find($data['variant_id']);

        // Only accept requests for currently unavailable variants.
        if ($variant && ! $variant->in_stock) {
            $notification = StockNotification::firstOrNew([
                'product_variant_id' => $variant->id,
                'email' => strtolower($data['email']),
            ]);
            $notification->notified_at = null; // (re)arm the alert
            $notification->save();
        }

        return back()->with('success', __('We will email you when this is back in stock.'));
    }
}
