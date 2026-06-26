<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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
            $cart->update(['expires_at' => $this->guestExpiry()]);

            return $this->resolved = $cart;
        }

        $token = (string) Str::uuid();
        Cookie::queue(self::COOKIE, $token, 60 * 24 * self::DAYS);

        return $this->resolved = Cart::create([
            'token' => $token,
            'expires_at' => $this->guestExpiry(),
        ]);
    }

    private function guestExpiry(): Carbon
    {
        return now()->addDays(self::DAYS);
    }

    /**
     * Record cart activity: bump updated_at and clear any abandoned-cart reminder so
     * a re-engaged cart can be reminded again if it is later abandoned.
     */
    private function markCartActive(Cart $cart): void
    {
        $cart->reminder_sent_at = null;
        $cart->touch();
    }

    /**
     * Delete expired guest carts (and their items). Returns the number of carts removed.
     */
    public function pruneExpired(): int
    {
        $expired = Cart::whereNull('user_id')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expired as $cart) {
            $cart->items()->delete();
            $cart->delete();
            $count++;
        }

        return $count;
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

        DB::transaction(function () use ($cart, $variant, $qty) {
            $item = $cart->items()
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first()
                ?? $cart->items()->make(['product_variant_id' => $variant->id]);

            $desired = ($item->exists ? $item->quantity : 0) + $qty;
            $item->quantity = $this->clampToStock($desired, $variant);
            $item->save();
        });

        $this->markCartActive($cart);
    }

    public function updateItem(CartItem $item, int $qty): void
    {
        $this->assertOwns($item);
        $this->markCartActive($this->current());

        if ($qty < 1) {
            $item->delete();

            return;
        }

        DB::transaction(function () use ($item, $qty) {
            $locked = CartItem::whereKey($item->getKey())->lockForUpdate()->first() ?? $item;
            $locked->quantity = $this->clampToStock($qty, $locked->variant);
            $locked->save();
        });
    }

    /**
     * Clamp a requested quantity to the variant's available stock (minimum 1).
     */
    private function clampToStock(int $qty, ProductVariant $variant): int
    {
        return min(max(1, $qty), max(1, $variant->stock_qty));
    }

    public function removeItem(CartItem $item): void
    {
        $this->assertOwns($item);
        $item->delete();
    }

    /**
     * Reconcile the cart against live stock before display/checkout: remove items
     * whose variant is missing, inactive or out of stock, and clamp any quantity
     * that now exceeds available stock. Returns true if the cart was modified.
     *
     * Keeps the cart view, the totals and checkout consistent; prevents a sold-out
     * item from deadlocking checkout (it was excluded from the total but blocked
     * order creation with no way to remove it); and guards the views — which read
     * $item->variant->... directly — against a removed variant.
     */
    public function reconcile(): bool
    {
        $cart = $this->current();
        $cart->loadMissing('items.variant');
        $changed = false;

        foreach ($cart->items as $item) {
            if (! $item->variant || ! $item->variant->in_stock) {
                $item->delete();
                $changed = true;

                continue;
            }

            if ($item->quantity > $item->variant->stock_qty) {
                $item->update(['quantity' => (int) $item->variant->stock_qty]);
                $changed = true;
            }
        }

        if ($changed) {
            $cart->load('items.variant.product');
        }

        return $changed;
    }

    public function applyCoupon(string $code): bool
    {
        $cart = $this->current();
        $cart->loadMissing('items.variant.product');

        // Promo codes are case-insensitive and whitespace-tolerant; match on a
        // canonical UPPER form so behaviour doesn't depend on DB collation, then
        // store the coupon's own (canonical) code on the cart.
        $code = strtoupper(trim($code));
        $coupon = Coupon::whereRaw('UPPER(code) = ?', [$code])->first();

        if (! $coupon || ! $coupon->isValidFor($this->purchasableSubtotalBaisa($cart))) {
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
     * @return array{subtotal:int, discount:int, shipping:int, tax:int, vat_percent:int, total:int, count:int}
     */
    public function summary(): array
    {
        $cart = $this->current();
        $cart->loadMissing('items.variant.product');

        $subtotal = $this->purchasableSubtotalBaisa($cart);
        $discount = $this->discountBaisa($cart, $subtotal);
        $payable = max(0, $subtotal - $discount);
        $shipping = $this->shippingBaisa($payable);
        $total = $payable + $shipping;

        // VAT is inclusive (displayed prices already contain it); break out the
        // component for transparency without changing what the customer pays.
        $vatPercent = (int) Setting::get('vat_percent', 5);
        $tax = $vatPercent > 0 ? (int) round($total * $vatPercent / (100 + $vatPercent)) : 0;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'vat_percent' => $vatPercent,
            'total' => $total,
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

    /**
     * Subtotal of only purchasable items: variant active and in stock. Inactive
     * or out-of-stock items remain in the cart but do not count toward totals.
     */
    private function purchasableSubtotalBaisa(Cart $cart): int
    {
        return (int) $cart->items
            ->filter(fn (CartItem $item) => $item->variant && $item->variant->in_stock)
            ->sum(fn (CartItem $item) => $item->lineTotalBaisa());
    }

    private function discountBaisa(Cart $cart, int $subtotal): int
    {
        if (! $cart->coupon_code) {
            return 0;
        }

        $coupon = Coupon::where('code', $cart->coupon_code)->first();

        // The stored code may have become stale (expired, deactivated, usage cap
        // reached, or the subtotal dropped below the minimum). Drop it and tell
        // the customer rather than silently keeping an inapplicable discount.
        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            $cart->update(['coupon_code' => null]);
            Session::flash('error', 'Your promo code is no longer valid and has been removed.');

            return 0;
        }

        return $coupon->discountFor($subtotal);
    }

    private function shippingBaisa(int $payable): int
    {
        if ($payable <= 0) {
            return 0;
        }

        $threshold = (int) Setting::get('free_shipping_threshold_baisa', 100000);
        $flat = (int) Setting::get('shipping_flat_baisa', 2000);

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
