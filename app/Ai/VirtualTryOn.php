<?php

namespace App\Ai;

use App\Exceptions\TryOnException;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Virtual try-on via Gemini's image model ("Nano Banana"). Sends the shopper's
 * photo + the product image and asks the model to dress the person in the garment,
 * returning the edited image as a base64 data URL.
 */
class VirtualTryOn
{
    private const PROMPT = <<<'TXT'
    You are a professional virtual fashion try-on tool for an online boutique.
    Image 1 is a photo of a person (the shopper). Image 2 is a clothing / fashion item.
    Generate exactly ONE photorealistic image of the SAME person from Image 1 now wearing
    the item from Image 2. Keep the person's face, hair, skin tone, body shape, pose and the
    background unchanged. Fit the garment naturally with correct lighting, perspective and
    fabric drape. Do not add any text, logos or watermarks. Output only the edited image.
    TXT;

    public function generate(string $shopperImageBase64, string $shopperMime, Product $product): string
    {
        [$productImageBase64, $productMime] = $this->productImage($product);

        $key = config('ai.providers.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('assistant.try_on.model', 'gemini-2.5-flash-image');
        $base = rtrim(config('ai.providers.gemini.url', 'https://generativelanguage.googleapis.com/v1beta/'), '/');

        if (! $key) {
            throw new TryOnException('failed', 'Virtual try-on is not configured yet.');
        }

        $response = Http::withHeaders(['x-goog-api-key' => $key])
            ->timeout((int) config('assistant.try_on.timeout', 90))
            ->post("{$base}/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['text' => self::PROMPT],
                        ['inline_data' => ['mime_type' => $shopperMime, 'data' => $shopperImageBase64]],
                        ['inline_data' => ['mime_type' => $productMime, 'data' => $productImageBase64]],
                    ],
                ]],
                'generationConfig' => ['responseModalities' => ['IMAGE', 'TEXT']],
            ]);

        if ($response->failed()) {
            $apiStatus = (string) data_get($response->json(), 'error.status');
            Log::warning('Virtual try-on API error', [
                'http' => $response->status(),
                'status' => $apiStatus,
                'message' => Str::limit((string) data_get($response->json(), 'error.message'), 300),
            ]);

            // Quota/billing problems (incl. the free-tier image model) → retryable.
            if ($response->status() === 429 || in_array($apiStatus, ['RESOURCE_EXHAUSTED', 'PERMISSION_DENIED'], true)) {
                throw new TryOnException('quota', 'Virtual try-on is temporarily unavailable. Please try again later.');
            }

            throw new TryOnException('failed', 'We could not create your try-on. Please try a clearer, well-lit photo.');
        }

        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            $data = $part['inlineData']['data'] ?? $part['inline_data']['data'] ?? null;

            if ($data) {
                $mime = $part['inlineData']['mimeType'] ?? $part['inline_data']['mime_type'] ?? 'image/png';

                return "data:{$mime};base64,{$data}";
            }
        }

        // No image came back — usually a safety block on the uploaded photo.
        $finish = (string) data_get($response->json(), 'candidates.0.finishReason');
        if (in_array($finish, ['SAFETY', 'IMAGE_SAFETY', 'PROHIBITED_CONTENT'], true)) {
            throw new TryOnException('failed', 'We could not process this photo. Please try a different one.');
        }

        throw new TryOnException('failed', 'We could not create your try-on. Please try another photo.');
    }

    /**
     * The product's primary image as [base64, mime] — read from local media when
     * possible, otherwise fetched from the (placeholder/remote) display URL.
     *
     * @return array{0: string, 1: string}
     */
    private function productImage(Product $product): array
    {
        $media = $product->getFirstMedia('gallery');

        // Cache the (potentially large) base64 payload so repeated try-on attempts
        // don't re-read the disk / re-fetch the URL and re-encode every time.
        $stamp = $media?->updated_at?->timestamp ?? 'noimg';

        return Cache::remember(
            "try_on:product_image:{$product->id}:{$stamp}",
            now()->addHour(),
            fn () => $this->resolveProductImage($product, $media),
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveProductImage(Product $product, $media): array
    {
        if ($media && is_file($media->getPath())) {
            return [base64_encode((string) file_get_contents($media->getPath())), $media->mime_type ?: 'image/jpeg'];
        }

        // Read a local public asset (e.g. placeholder images) straight from disk —
        // avoids an HTTP round-trip (and a dev-server deadlock) and ignores APP_URL.
        $url = $product->displayImageUrl();
        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $local = public_path($path);

        if ($path !== '' && is_file($local)) {
            return [base64_encode((string) file_get_contents($local)), mime_content_type($local) ?: 'image/jpeg'];
        }

        $response = Http::timeout(20)->get($url);

        if ($response->failed()) {
            throw new TryOnException('failed', 'This product image is not available for try-on.');
        }

        return [base64_encode($response->body()), $response->header('Content-Type') ?: 'image/jpeg'];
    }
}
