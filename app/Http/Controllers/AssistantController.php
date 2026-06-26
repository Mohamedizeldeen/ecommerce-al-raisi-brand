<?php

namespace App\Http\Controllers;

use App\Ai\Agents\StorefrontAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Enums\Lab;
use Throwable;

class AssistantController extends Controller
{
    /**
     * Handle one turn of the storefront chat assistant.
     *
     * The browser sends the new message plus the recent conversation history;
     * we ground the reply in store data via the agent's tools and return JSON.
     */
    public function chat(Request $request): JsonResponse
    {
        abort_unless(config('assistant.enabled', true), 404);

        // Validate manually so this endpoint ALWAYS answers with JSON (never a
        // redirect), regardless of the request's Accept headers.
        $validator = Validator::make($request->all(), [
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:50'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:4000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid request.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Cost guard: a per-IP daily cap plus a global daily budget, mirroring the
        // try-on endpoint so an attacker rotating IPs (under the 20/min throttle)
        // can't drive unbounded Gemini spend. Cache::add seeds the counter first —
        // the database cache store won't create a key on increment alone.
        $perKey = 'assistant:'.$request->ip().':'.now()->format('Y-m-d');
        $budgetKey = 'assistant:'.now()->format('Y-m-d');
        Cache::add($perKey, 0, now()->addHours(25));
        Cache::add($budgetKey, 0, now()->addHours(25));

        if ((int) Cache::increment($perKey) > (int) config('assistant.per_client_daily', 50)
            || (int) Cache::increment($budgetKey) > (int) config('assistant.daily_limit', 1000)) {
            return response()->json([
                'reply' => __('The assistant is busy right now — please try again in a little while.'),
            ], 503);
        }

        $history = collect($validated['history'] ?? [])
            ->map(fn (array $turn): array => [
                'role' => $turn['role'],
                'content' => $turn['content'],
            ])
            ->slice(-1 * config('assistant.max_history', 8))
            ->values()
            ->all();

        try {
            $response = (new StorefrontAssistant($history))->prompt(
                $validated['message'],
                provider: Lab::Gemini,
                model: config('assistant.model'),
                timeout: config('assistant.timeout', 30),
            );

            return response()->json([
                'reply' => trim((string) $response),
            ]);
        } catch (Throwable $e) {
            report($e);

            $phone = config('assistant.fallback_phone');

            return response()->json([
                'reply' => "عذراً، حدث خلل مؤقت. يُرجى التواصل معنا على {$phone}.\n"
                    ."Sorry, something went wrong. Please contact us on {$phone}.",
            ]);
        }
    }
}
