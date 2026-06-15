<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    private const COOKIE = 'cart_token';

    private const DAYS = 30;

    private ?Cart $resolved = null;

    /**
     * Resolve (creating if necessary) the cart for the current visitor.
     */
    public function current(): Cart
    {
        if ($this->resolved) {
            return $this->resolved;
        }

        if (Auth::check()) {
            return $this->resolved = Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $token = request()->cookie(self::COOKIE);

        if ($token && $cart = Cart::whereNull('user_id')->where('token', $token)->first()) {
            return $this->resolved = $cart;
        }

        $token = (string) Str::uuid();
        Cookie::queue(self::COOKIE, $token, 60 * 24 * self::DAYS);

        return $this->resolved = Cart::create(['token' => $token]);
    }

    /**
     * Return the existing cart without creating one (used for the header count).
     */
    public function existing(): ?Cart
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->first();
        }

        $token = request()->cookie(self::COOKIE);

        return $token ? Cart::whereNull('user_id')->where('token', $token)->first() : null;
    }

    public function count(): int
    {
        // Prefer the cart already resolved this request (e.g. just created during
        // an add) — the guest cookie is only queued on the response, so existing()
        // would miss a brand-new cart on the very first add.
        $cart = $this->resolved ?? $this->existing();

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    public function add(ProductVariant $variant, int $qty = 1): void
    {
        $cart = $this->current();
        $qty = max(1, $qty);

        $item = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
        $desired = ($item->exists ? $item->quantity : 0) + $qty;
        $item->quantity = min($desired, max(1, $variant->stock_qty));
        $item->save();
    }

    public function updateItem(CartItem $item, int $qty): void
    {
        $this->assertOwns($item);

        if ($qty < 1) {
            $item->delete();

            return;
        }

        $item->quantity = min($qty, max(1, $item->variant->stock_qty));
        $item->save();
    }

    public function removeItem(CartItem $item): void
    {
        $this->assertOwns($item);
        $item->delete();
    }

    public function applyCoupon(string $code): bool
    {
        $cart = $this->current();
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor($cart->subtotalBaisa())) {
            return false;
        }

        $cart->update(['coupon_code' => $coupon->code]);

        return true;
    }

    public function clearCoupon(): void
    {
        $this->current()->update(['coupon_code' => null]);
    }

    /**
     * @return array{subtotal:int, discount:int, shipping:int, total:int, count:int}
     */
    public function summary(): array
    {
        $cart = $this->current();
        $cart->loadMissing('items.variant.product');

        $subtotal = $cart->subtotalBaisa();
        $discount = $this->discountBaisa($cart, $subtotal);
        $payable = max(0, $subtotal - $discount);
        $shipping = $this->shippingBaisa($payable);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $payable + $shipping,
            'count' => $cart->totalQuantity(),
        ];
    }

    public function clear(): void
    {
        $cart = $this->existing();

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);
        }
    }

    /**
     * Merge a guest's cookie-based cart into the authenticated user's cart on login.
     */
    public function mergeGuestIntoUser(User $user): void
    {
        $token = request()->cookie(self::COOKIE);

        if (! $token) {
            return;
        }

        $guest = Cart::whereNull('user_id')->where('token', $token)->with('items')->first();

        if (! $guest) {
            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($guest->items as $item) {
            $existing = $userCart->items()->where('product_variant_id', $item->product_variant_id)->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
            } else {
                $userCart->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                ]);
            }
        }

        if ($guest->coupon_code && ! $userCart->coupon_code) {
            $userCart->update(['coupon_code' => $guest->coupon_code]);
        }

        $guest->items()->delete();
        $guest->delete();
        Cookie::queue(Cookie::forget(self::COOKIE));
        $this->resolved = null;
    }

    private function discountBaisa(Cart $cart, int $subtotal): int
    {
        if (! $cart->coupon_code) {
            return 0;
        }

        $coupon = Coupon::where('code', $cart->coupon_code)->first();

        return $coupon ? $coupon->discountFor($subtotal) : 0;
    }

    private function shippingBaisa(int $payable): int
    {
        if ($payable <= 0) {
            return 0;
        }

        $threshold = (int) Setting::get('free_shipping_threshold_baisa', 0);
        $flat = (int) Setting::get('shipping_flat_baisa', 0);

        if ($threshold > 0 && $payable >= $threshold) {
            return 0;
        }

        return $flat;
    }

    private function assertOwns(CartItem $item): void
    {
        abort_unless($item->cart_id === $this->current()->id, 403);
    }
}
