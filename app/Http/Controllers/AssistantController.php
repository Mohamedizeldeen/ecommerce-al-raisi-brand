<?php

namespace App\Http\Controllers;

use App\Ai\Agents\StorefrontAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
