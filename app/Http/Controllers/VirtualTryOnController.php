<?php

namespace App\Http\Controllers;

use App\Ai\VirtualTryOn;
use App\Exceptions\TryOnException;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Throwable;

class VirtualTryOnController extends Controller
{
    public function __construct(private readonly VirtualTryOn $tryOn) {}

    /**
     * Take the shopper's uploaded photo + this product and return an AI try-on image.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        abort_unless(config('assistant.try_on.enabled', true), 404);
        abort_unless($product->is_active, 404);

        // Validate manually so the endpoint always answers JSON (never a redirect).
        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => __('Please upload a clear photo (JPG, PNG or WebP, up to 8MB).'),
            ], 422);
        }

        // Per-client (per-IP) daily cap — checked BEFORE the global budget so only
        // clients still under their own quota spend the shared daily allowance.
        // Same Cache::add-then-increment pattern (the DB cache store won't create
        // a key on increment alone, which would otherwise defeat the guard).
        $perKey = 'try_on:'.$request->ip().':'.now()->format('Y-m-d');
        Cache::add($perKey, 0, now()->addHours(25));

        if ((int) Cache::increment($perKey) > (int) config('assistant.try_on.per_client_daily', 10)) {
            return response()->json([
                'error' => __('Virtual try-on is unavailable right now. Please try again later.'),
            ], 503);
        }

        // Global daily budget guard (cost control) — count every accepted attempt.
        // Cache::add seeds the counter row first (the database/file stores don't
        // auto-create a key on increment, which would otherwise defeat the guard).
        $budgetKey = 'try_on:'.now()->format('Y-m-d');
        Cache::add($budgetKey, 0, now()->addHours(25));
        $used = (int) Cache::increment($budgetKey);

        if ($used > (int) config('assistant.try_on.daily_limit', 200)) {
            return response()->json([
                'error' => __('Virtual try-on is unavailable right now. Please try again later.'),
            ], 503);
        }

        $photo = $request->file('photo');

        try {
            $image = $this->tryOn->generate(
                base64_encode((string) $photo->get()),
                $photo->getMimeType() ?: 'image/jpeg',
                $product,
            );

            return response()->json(['image' => $image]);
        } catch (TryOnException $e) {
            return response()->json(['error' => __($e->userMessage)], $e->kind === 'quota' ? 503 : 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => __('Virtual try-on is unavailable right now. Please try again later.')], 500);
        }
    }
}
